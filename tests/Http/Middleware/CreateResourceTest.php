<?php

namespace Junction\Api\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Resource\ResourceInterface;
use Meritum\Serialization\Resource\Collection as ResourceCollection;
use Meritum\Serialization\SerializerInterface;
use Junction\Api\Http\Middleware\CreateResource;

final class CreateResourceTest extends TestCase
{
    public function test_throws_when_data_attribute_is_null(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(\LogicException::class);

        (new CreateResource($this->createMock(SerializerInterface::class)))->process($request, $handler);
    }

    public function test_wraps_object_as_item(): void
    {
        $data = new \stdClass();

        $capturedResource = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($data);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$capturedResource) {
                $capturedResource = $value;
                return $newRequest;
            });

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->with($newRequest)->willReturn($response);

        $result = (new CreateResource($this->createMock(SerializerInterface::class)))->process($request, $handler);

        $this->assertInstanceOf(Item::class, $capturedResource);
        $this->assertSame($response, $result);
    }

    public function test_wraps_array_as_collection(): void
    {
        $data = [new \stdClass(), new \stdClass()];

        $capturedResource = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($data);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$capturedResource) {
                $capturedResource = $value;
                return $newRequest;
            });

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new CreateResource($this->createMock(SerializerInterface::class)))->process($request, $handler);

        $this->assertInstanceOf(ResourceCollection::class, $capturedResource);
    }

    public function test_wraps_cursor_paginator_as_paginated_collection(): void
    {
        $paginator = new CursorPaginator(new Collection([]), 'next-tok', 'prev-tok', 25);

        $capturedResource = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($paginator);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$capturedResource) {
                $capturedResource = $value;
                return $newRequest;
            });

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new CreateResource($this->createMock(SerializerInterface::class)))->process($request, $handler);

        $this->assertInstanceOf(ResourceCollection::class, $capturedResource);
        assert($capturedResource instanceof ResourceCollection);

        $pagination = $capturedResource->getPagination();
        $this->assertSame('next-tok', $pagination['next']);
        $this->assertSame('prev-tok', $pagination['previous']);
        $this->assertSame(25, $pagination['limit']);
    }

    public function test_sets_resource_with_correct_attribute_key(): void
    {
        $data = new \stdClass();
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $capturedKey = null;
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('data')->willReturn($data);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$capturedKey) {
                $capturedKey = $key;
                return $newRequest;
            });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new CreateResource($this->createMock(SerializerInterface::class)))->process($request, $handler);

        $this->assertSame(ResourceInterface::class, $capturedKey);
    }
}
