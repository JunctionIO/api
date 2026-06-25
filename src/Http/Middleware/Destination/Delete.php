<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\Command\Delete as DeleteCommand;

final class Delete implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        $this->dispatcher->dispatch(new DeleteCommand($id));

        return $handler->handle($request);
    }
}
