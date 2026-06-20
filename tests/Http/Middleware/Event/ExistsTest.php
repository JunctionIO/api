<?php

namespace Junction\Api\Test\Http\Middleware\Event;

use Meritum\Http\Exception\NotFoundHttpException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Http\Middleware\Event\Exists;

final class ExistsTest extends TestCase
{
    public function test_delegates_to_handler_when_event_exists(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('exists')->willReturn(true);

        $result = (new Exists($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_throws_not_found_when_event_does_not_exist(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('exists')->willReturn(false);

        $this->expectException(NotFoundHttpException::class);

        (new Exists($repo))->process($request, $handler);
    }

    public function test_passes_id_attribute_to_repository(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('exists')
            ->with('550e8400-e29b-41d4-a716-446655440000')
            ->willReturn(true);

        (new Exists($repo))->process($request, $handler);
    }
}
