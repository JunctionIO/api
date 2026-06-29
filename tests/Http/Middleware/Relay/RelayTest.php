<?php

namespace Junction\Api\Test\Http\Middleware\Relay;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Http\Middleware\Relay\Relay;
use Junction\Api\Relay\Command\Relay as RelayCommand;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RelayTest extends TestCase
{
    private function makeDestination(string $id = 'dest-uuid'): Destination
    {
        return new Destination([
            'id'                  => $id,
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    private function makeEvent(?Collection $destinations = null): Event
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'order.placed']);
        $event->setDestinations($destinations ?? new Collection([]));

        return $event;
    }

    private function makeLog(
        string $traceId = 'trace-uuid',
        string $id = 'log-uuid',
        array $payload = ['foo' => 'bar'],
    ): EventLog {
        return new EventLog(['id' => $id, 'trace_id' => $traceId, 'event_id' => 'event-uuid', 'auth_id' => 'auth-id', 'payload' => $payload]);
    }

    private function makeRequest(?Event $event = null, ?EventLog $log = null): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [Event::class,    null, $event ?? $this->makeEvent()],
            [EventLog::class, null, $log   ?? $this->makeLog()],
        ]);

        return $request;
    }

    private function captureCommand(ServerRequestInterface $request): RelayCommand
    {
        $captured = null;

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($cmd) use (&$captured) {
            $captured = $cmd;
        });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Relay($dispatcher))->process($request, $handler);

        return $captured;
    }

    public function test_dispatches_relay_command_with_trace_id(): void
    {
        $log = $this->makeLog(traceId: 'my-trace-id');
        $cmd = $this->captureCommand($this->makeRequest(log: $log));

        $this->assertInstanceOf(RelayCommand::class, $cmd);
        $this->assertSame('my-trace-id', $cmd->traceId);
    }

    public function test_dispatches_relay_command_with_log_id(): void
    {
        $log = $this->makeLog(id: 'my-log-id');
        $cmd = $this->captureCommand($this->makeRequest(log: $log));

        $this->assertSame('my-log-id', $cmd->logId);
    }

    public function test_dispatches_relay_command_with_payload(): void
    {
        $payload = ['amount' => 99, 'currency' => 'USD'];
        $log     = $this->makeLog(payload: $payload);
        $cmd     = $this->captureCommand($this->makeRequest(log: $log));

        $this->assertSame($payload, $cmd->payload);
    }

    public function test_dispatches_relay_command_with_event_destinations(): void
    {
        $destinations = new Collection(['dest-uuid' => $this->makeDestination()]);
        $event        = $this->makeEvent($destinations);
        $cmd          = $this->captureCommand($this->makeRequest(event: $event));

        $this->assertSame($destinations, $cmd->destinations);
    }

    public function test_passes_request_to_handler(): void
    {
        $request = $this->makeRequest();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        (new Relay($dispatcher))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response   = $this->createMock(ResponseInterface::class);
        $dispatcher = $this->createMock(DispatcherInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Relay($dispatcher))->process($this->makeRequest(), $handler);

        $this->assertSame($response, $result);
    }
}
