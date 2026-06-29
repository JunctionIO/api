<?php

namespace Junction\Api\Http\Middleware\Relay;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Exception\BadRequestHttpException;

final class ValidateEvent implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $event = $request->getHeaderLine('X-Junction-Event');

        if ('' === $event || !preg_match('/^[a-zA-Z0-9._-]+$/', $event)) {
            throw new BadRequestHttpException(
                $request,
                'The X-Junction-Event header is missing or provided an invalid value'
            );
        }

        $request = $request->withAttribute('event_name', $event);

        return $handler->handle($request);
    }
}
