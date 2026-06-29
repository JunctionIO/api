<?php

namespace Junction\Api\Http\Middleware\Relay;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Relay\Command\Relay as Command;

final class Relay implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $event = $request->getAttribute(Event::class);

        $log = $request->getAttribute(EventLog::class);

        assert($event instanceof Event);
        assert($log instanceof EventLog);

        $this->dispatcher->dispatch(new Command(
            $log->traceId,
            $log->id,
            $log->payload,
            $event->getDestinations()
        ));

        return $handler->handle($request);
    }
}
