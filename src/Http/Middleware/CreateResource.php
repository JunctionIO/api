<?php

namespace Junction\Api\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Meritum\Serialization\Resource\Item;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Database\Support\CursorPaginator;
use Meritum\Serialization\Resource\Collection;
use Meritum\Serialization\SerializerInterface;
use Junction\Api\Support\CursorPaginatorAdaptor;
use Meritum\Serialization\Resource\ResourceInterface;

final class CreateResource implements MiddlewareInterface
{
    public function __construct(private readonly SerializerInterface $serializer) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute('data');

        if (null === $data) {
            throw new \LogicException('Data staged for serialization cannot be null');
        }

        $resource = match (true) {
            $data instanceof CursorPaginator => $this->paginatedCollection($data),
            is_iterable($data)               => $this->collection($data),
            default                          => $this->item($data)
        };

        $request = $request->withAttribute(ResourceInterface::class, $resource);

        return $handler->handle($request);
    }

    private function item(mixed $data): Item
    {
        return new Item($data, $this->serializer);
    }

    /**
     * @param iterable<mixed> $data
     */
    private function collection(iterable $data): Collection
    {
        return new Collection($data, $this->serializer);
    }

    /**
     * @param CursorPaginator<\Meritum\Database\Model> $cursor
     */
    private function paginatedCollection(CursorPaginator $cursor): Collection
    {
        $collection = $this->collection($cursor->collection());

        $collection->setCursor(new CursorPaginatorAdaptor($cursor));

        return $collection;
    }
}
