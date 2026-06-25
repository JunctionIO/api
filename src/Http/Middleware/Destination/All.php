<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Support\CursorParams;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\Command\QueryAll;

final class All implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getAttribute(CursorParams::class);

        assert($params instanceof CursorParams);

        $request = $request->withAttribute(
            'data',
            $this->dispatcher->dispatch(new QueryAll($params->limit, $params->cursor))
        );

        return $handler->handle($request);
    }
}
