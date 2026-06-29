<?php

namespace Junction\Api\Http\Middleware\Relay;

use Junction\Api\Event\Event;
use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\Relay\Command\QueryEvent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindEvent implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $name = $request->getAttribute('event_name', '');

        assert(is_string($name));

        $model = $this->dispatcher->dispatch(new QueryEvent($name));

        $request = $request->withAttribute(Event::class, $model);

        return $handler->handle($request);
    }
}
