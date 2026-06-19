<?php

namespace Junction\Api\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SetStatusCode implements MiddlewareInterface
{
    public function __construct(private readonly int $status) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('status', $this->status);

        return $handler->handle($request);
    }
}
