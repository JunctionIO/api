<?php

namespace Junction\Api\Http\Middleware;

use Meritum\Validation\Rule\Uuid;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Http\Exception\NotFoundHttpException;

final class ValidateUuidId implements MiddlewareInterface
{
    public function __construct(private readonly Uuid $uuid) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $id */
        $id = $request->getAttribute('id', '');

        if (false === $this->uuid->validate($id)) {
            throw new NotFoundHttpException($request, 'The requested resource was not found');
        }

        return $handler->handle($request);
    }
}
