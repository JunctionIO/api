<?php

namespace Junction\Api\Http\Middleware\DestinationType;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class All implements MiddlewareInterface
{
    public function __construct(
        private readonly DestinationTypeRepositoryInterface $repo
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $this->repo->all();

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
