<?php

namespace Junction\Api\Http\Middleware\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Meritum\Http\Exception\NotFoundHttpException;

final class Exists implements MiddlewareInterface
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $id */
        $id = $request->getAttribute('id', '');

        if (false === $this->repo->exists($id)) {
            throw new NotFoundHttpException($request, "Event with ID [{$id}] was not found");
        }

        return $handler->handle($request);
    }
}
