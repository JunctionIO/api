<?php

namespace Junction\Api\Http\Middleware\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;

final class Find implements MiddlewareInterface
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        $model = $this->repo->findOrFail($id);

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
