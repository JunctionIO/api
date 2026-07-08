<?php

namespace Junction\Api\Test\Unit\Http\Middleware\DestinationType;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Http\Middleware\DestinationType\Find;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindTest extends TestCase
{
    public function test_sets_model_as_data_attribute_when_found(): void
    {
        $model      = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'junction.destinations.http']);
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('uuid-123');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($newRequest);

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Find($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_id_to_repository(): void
    {
        $model = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'junction.destinations.http']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('uuid-123');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('uuid-123')
            ->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Find($repo))->process($request, $handler);
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('uuid-123');

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(ModelNotFoundException::class);

        (new Find($repo))->process($request, $handler);
    }
}
