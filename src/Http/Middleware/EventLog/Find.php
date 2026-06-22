<?php

namespace Junction\Api\Http\Middleware\EventLog;

use Junction\Api\EventLog\EventLog;
use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\EventLog\Command\QueryFind;

final class Find implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $bus) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        $model = $this->bus->dispatch(new QueryFind($id));

        assert($model instanceof EventLog);

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
