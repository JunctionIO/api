<?php

namespace Junction\Api\Http\Middleware\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Meritum\Http\Exception\NotFoundHttpException;

final class Find implements MiddlewareInterface
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $id */
        $id = $request->getAttribute('id', '');

        $model = $this->repo->find($id);

        if (null === $model) {
            throw new NotFoundHttpException($request, "Event with ID [{$id}] was not found");
        }

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
