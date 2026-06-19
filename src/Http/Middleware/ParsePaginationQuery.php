<?php

namespace Junction\Api\Http\Middleware;

use Junction\Api\Support\CursorParams;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ParsePaginationQuery implements MiddlewareInterface
{
    public function __construct(
        private readonly int $default = 25,
        private readonly int $max = 100
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var array<string, string> $query */
        $query = $request->getQueryParams();

        $params = new CursorParams($query, $this->default, $this->max);

        $request = $request->withAttribute(CursorParams::class, $params);

        return $handler->handle($request);
    }
}
