<?php

namespace Junction\Api\Http\Middleware\DestinationLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Support\CursorParams;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationLog\Command\QueryAllForDestination;

final class AllForDestination implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        $params = $request->getAttribute(CursorParams::class);

        assert(is_string($id));
        assert($params instanceof CursorParams);

        $data = $this->dispatcher->dispatch(new QueryAllForDestination($id, $params->limit, $params->cursor));

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
