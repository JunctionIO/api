<?php

namespace Junction\Api\Test\Http\Middleware\DestinationLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\DestinationLog\Command\Update as UpdateCommand;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\Http\Middleware\DestinationLog\Update;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UpdateTest extends TestCase
{
    private function makeLog(): DestinationLog
    {
        return new DestinationLog([
            'id'             => 'dlog-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'elog-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'dispatched',
            'created_at'     => '2026-06-26 10:00:00',
            'updated_at'     => '2026-06-26 10:00:00',
        ]);
    }

    private function makeBody(): array
    {
        return [
            'log_id'       => 'dlog-uuid',
            'status'       => 'dispatched',
            'attempted_at' => '2026-06-26 10:00:00',
        ];
    }

    public function test_dispatches_update_command_with_body_data(): void
    {
        $model = $this->makeLog();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(UpdateCommand $cmd) => $cmd->id === 'dlog-uuid'
                    && $cmd->status === 'dispatched'
                    && $cmd->attemptedAt === '2026-06-26 10:00:00'
                    && $cmd->error === null
            ))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($this->makeBody());
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Update($dispatcher))->process($request, $handler);
    }

    public function test_dispatches_with_error_when_present(): void
    {
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(UpdateCommand $cmd) => $cmd->error === 'Connection refused'))
            ->willReturn($this->makeLog());

        $body = $this->makeBody() + ['error' => 'Connection refused'];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Update($dispatcher))->process($request, $handler);
    }

    public function test_sets_result_as_data_attribute(): void
    {
        $model      = $this->makeLog();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($this->makeBody());
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Update($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeLog());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($this->makeBody());
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Update($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
