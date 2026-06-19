<?php

namespace Junction\Api\Http\Middleware\Event;

use Junction\Api\Support\CursorParams;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;

final class All implements MiddlewareInterface
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getAttribute(CursorParams::class);

        assert($params instanceof CursorParams);

        $cursor = $this->repo->all($params->limit, $params->cursor);

        $request = $request->withAttribute('data', $cursor);

        return $handler->handle($request);
    }
}
