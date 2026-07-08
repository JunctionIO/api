<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\QueryFind;
use Junction\Api\Destination\Destination;
use Junction\Api\Http\Middleware\Destination\Find;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindTest extends TestCase
{
    private function makeModel(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    public function test_dispatches_query_find_with_route_id(): void
    {
        $model = $this->makeModel();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(QueryFind $cmd) => $cmd->id === 'dest-uuid'
            ))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Find($dispatcher))->process($request, $handler);
    }

    public function test_sets_model_as_data_attribute(): void
    {
        $model      = $this->makeModel();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Find($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeModel());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Find($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
