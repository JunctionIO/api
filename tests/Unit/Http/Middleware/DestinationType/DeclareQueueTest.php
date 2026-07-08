<?php

namespace Junction\Api\Test\Unit\Http\Middleware\DestinationType;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\DestinationType\DeclareQueue;
use Junction\Api\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DeclareQueueTest extends TestCase
{
    private function makeModel(): DestinationType
    {
        return new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'webhook',
            'queue'         => 'junction.destinations.webhook',
            'description'   => null,
            'config_schema' => [],
            'created_at'    => '2026-06-25 10:00:00',
            'updated_at'    => '2026-06-25 10:00:00',
        ]);
    }

    public function test_declares_queue_from_model(): void
    {
        $model = $this->makeModel();

        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->once())
            ->method('declare')
            ->with('junction.destinations.webhook');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new DeclareQueue($queue))->process($request, $handler);
    }

    public function test_delegates_to_handler_after_declare(): void
    {
        $response   = $this->createMock(ResponseInterface::class);
        $model      = $this->makeModel();

        $queue = $this->createMock(QueueInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        (new DeclareQueue($queue))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $model    = $this->makeModel();

        $queue = $this->createMock(QueueInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new DeclareQueue($queue))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
