<?php

namespace Junction\Api\Test\Http\Middleware\Relay;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\ApiToken\Token;
use Junction\Api\Event\Event;
use Junction\Api\EventLog\Command\Create;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Http\Middleware\Relay\CreateEventLog;
use Junction\Api\Trace\TraceId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateEventLogTest extends TestCase
{
    private string $traceUuid = '550e8400-e29b-41d4-a716-446655440000';

    private function makeTraceId(): TraceId
    {
        $id = new TraceId();
        $id->set($this->traceUuid);

        return $id;
    }

    private function makeEvent(string $id = 'event-uuid'): Event
    {
        return new Event(['id' => $id, 'name' => 'order.placed']);
    }

    private function makeToken(string $id = 'auth-id'): Token
    {
        return new Token($id, 'relay', 1700000000);
    }

    private function makeLog(): EventLog
    {
        return new EventLog(['id' => 'log-uuid', 'trace_id' => $this->traceUuid, 'event_id' => 'event-uuid', 'auth_id' => 'auth-id', 'payload' => []]);
    }

    private function makeRequest(
        ?TraceId $traceId = null,
        ?Event $event = null,
        ?Token $token = null,
        string $clientIp = '10.0.0.1',
        array $payload = ['foo' => 'bar'],
    ): ServerRequestInterface {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [TraceId::class, null, $traceId ?? $this->makeTraceId()],
            [Event::class,   null, $event   ?? $this->makeEvent()],
            [Token::class,   null, $token   ?? $this->makeToken()],
        ]);
        $request->method('getHeaderLine')->with('X-Client-IP')->willReturn($clientIp);
        $request->method('getParsedBody')->willReturn(['payload' => $payload]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        return $request;
    }

    private function captureCommand(ServerRequestInterface $request, ?EventLog $log = null): Create
    {
        $captured   = null;
        $log      ??= $this->makeLog();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($cmd) use (&$captured, $log) {
            $captured = $cmd;

            return $log;
        });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new CreateEventLog($dispatcher))->process($request, $handler);

        return $captured;
    }

    public function test_dispatches_create_with_trace_id(): void
    {
        $cmd = $this->captureCommand($this->makeRequest(traceId: $this->makeTraceId()));

        $this->assertInstanceOf(Create::class, $cmd);
        $this->assertSame($this->traceUuid, $cmd->traceId);
    }

    public function test_dispatches_create_with_event_id(): void
    {
        $cmd = $this->captureCommand($this->makeRequest(event: $this->makeEvent('my-event-uuid')));

        $this->assertSame('my-event-uuid', $cmd->eventId);
    }

    public function test_dispatches_create_with_auth_id_from_token(): void
    {
        $cmd = $this->captureCommand($this->makeRequest(token: $this->makeToken('prod-relay')));

        $this->assertSame('prod-relay', $cmd->authId);
    }

    public function test_dispatches_create_with_source_ip(): void
    {
        $cmd = $this->captureCommand($this->makeRequest(clientIp: '192.168.1.10'));

        $this->assertSame('192.168.1.10', $cmd->sourceIp);
    }

    public function test_source_ip_is_null_when_header_is_empty(): void
    {
        $cmd = $this->captureCommand($this->makeRequest(clientIp: ''));

        $this->assertNull($cmd->sourceIp);
    }

    public function test_dispatches_create_with_payload(): void
    {
        $payload = ['order' => ['id' => 'ord-1', 'amount' => 99]];
        $cmd     = $this->captureCommand($this->makeRequest(payload: $payload));

        $this->assertSame($payload, $cmd->payload);
    }

    public function test_sets_event_log_as_request_attribute(): void
    {
        $log            = $this->makeLog();
        $updatedRequest = $this->createMock(ServerRequestInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($log);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [TraceId::class, null, $this->makeTraceId()],
            [Event::class,   null, $this->makeEvent()],
            [Token::class,   null, $this->makeToken()],
        ]);
        $request->method('getHeaderLine')->willReturn('10.0.0.1');
        $request->method('getParsedBody')->willReturn(['payload' => []]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(EventLog::class, $log)
            ->willReturn($updatedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new CreateEventLog($dispatcher))->process($request, $handler);
    }

    public function test_passes_updated_request_to_handler(): void
    {
        $updatedRequest = $this->createMock(ServerRequestInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeLog());

        $request = $this->makeRequest();
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [TraceId::class, null, $this->makeTraceId()],
            [Event::class,   null, $this->makeEvent()],
            [Token::class,   null, $this->makeToken()],
        ]);
        $request->method('getHeaderLine')->willReturn('10.0.0.1');
        $request->method('getParsedBody')->willReturn(['payload' => []]);
        $request->method('withAttribute')->willReturn($updatedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($updatedRequest)
            ->willReturn($this->createMock(ResponseInterface::class));

        (new CreateEventLog($dispatcher))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeLog());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new CreateEventLog($dispatcher))->process($this->makeRequest(), $handler);

        $this->assertSame($response, $result);
    }
}
