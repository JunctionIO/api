<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Event;

use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Http\Middleware\Event\Find;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindTest extends TestCase
{
    public function test_sets_model_as_data_attribute_when_found(): void
    {
        $model          = new Event(['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'test.event']);
        $updatedRequest = $this->createMock(ServerRequestInterface::class);
        $response       = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($updatedRequest);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($updatedRequest)->willReturn($response);

        $result = (new Find($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(ModelNotFoundException::class);

        (new Find($repo))->process($request, $handler);
    }

    public function test_passes_id_attribute_to_repository(): void
    {
        $model = new Event(['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'test.event']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('550e8400-e29b-41d4-a716-446655440000')
            ->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Find($repo))->process($request, $handler);
    }
}
