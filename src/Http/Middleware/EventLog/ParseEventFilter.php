<?php

namespace Junction\Api\Http\Middleware\EventLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;

final class ParseEventFilter implements MiddlewareInterface
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $query  = $request->getQueryParams();
        $filter = $query['filter'] ?? null;

        if (is_array($filter) && isset($filter['event'])) {
            $events = [];

            foreach ((array) $filter['event'] as $name) {
                if (is_string($name)) {
                    $events[] = $name;
                }
            }

            $collection = $this->repo->getByName($events, ['id']);

            $request = $request->withAttribute('event_ids', $collection->keys());
        }

        return $handler->handle($request);
    }
}
