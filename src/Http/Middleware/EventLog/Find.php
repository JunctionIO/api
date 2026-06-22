<?php

namespace Junction\Api\Http\Middleware\EventLog;

use Junction\Api\EventLog\EventLog;
use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\EventLog\Command\QueryFind;
use Meritum\Http\Exception\NotFoundHttpException;

final class Find implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $bus) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $id */
        $id = $request->getAttribute('id', '');

        /** @var EventLog|null $model */
        $model = $this->bus->dispatch(new QueryFind($id));

        if (null === $model) {
            throw new NotFoundHttpException($request, "Event Log with ID [{$id}] was not found");
        }

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
