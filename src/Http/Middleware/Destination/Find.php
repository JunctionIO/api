<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\Command\QueryFind;

final class Find implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        $data = $this->dispatcher->dispatch(new QueryFind($id));

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
