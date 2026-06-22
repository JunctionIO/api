<?php

namespace Junction\Api\Http\Middleware\EventLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Support\CursorParams;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\EventLog\Command\QueryAll;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class All implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $bus) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getAttribute(CursorParams::class);

        /** @var null|string[] $eventIds */
        $eventIds = $request->getAttribute('event_ids');

        assert($params instanceof CursorParams);

        $data = $this->bus->dispatch(new QueryAll($params->limit, $params->cursor, $eventIds));

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
