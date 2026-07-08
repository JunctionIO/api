<?php

namespace Junction\Api\Test\Unit\Http\Middleware\DestinationType;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Http\Middleware\DestinationType\All;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AllTest extends TestCase
{
    public function test_sets_collection_as_data_attribute(): void
    {
        $collection = new Collection([]);
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $collection)
            ->willReturn($newRequest);

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('all')->willReturn($collection);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new All($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_calls_all_on_repository(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->once())->method('all')->willReturn(new Collection([]));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new All($repo))->process($request, $handler);
    }
}
