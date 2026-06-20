<?php

namespace Junction\Api\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Exception\UnsupportedMediaTypeHttpException;

final class ValidateContentType implements MiddlewareInterface
{
    private const array WRITE_METHODS = ['POST', 'PUT', 'PATCH'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), self::WRITE_METHODS, true)) {
            $mediaType = strtolower(trim(explode(';', $request->getHeaderLine('Content-Type'))[0]));

            if ('application/json' !== $mediaType) {
                throw new UnsupportedMediaTypeHttpException($request, 'Content-Type must be application/json');
            }
        }

        return $handler->handle($request);
    }
}
