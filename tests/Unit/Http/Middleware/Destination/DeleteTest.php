<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\Delete as DeleteCommand;
use Junction\Api\Http\Middleware\Destination\Delete;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DeleteTest extends TestCase
{
    public function test_dispatches_delete_command_with_route_id(): void
    {
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(DeleteCommand $cmd) => $cmd->id === 'dest-uuid'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Delete($dispatcher))->process($request, $handler);
    }

    public function test_passes_original_request_to_handler(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        (new Delete($dispatcher))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Delete($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
