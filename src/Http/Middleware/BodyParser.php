<?php

namespace Junction\Api\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Exception\BadRequestHttpException;

final class BodyParser implements MiddlewareInterface
{
    private const array WRITE_METHODS = ['POST', 'PUT', 'PATCH'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), self::WRITE_METHODS, true)) {
            $data = json_decode((string) $request->getBody(), true);

            if (false === is_array($data)) {
                throw new BadRequestHttpException($request, 'Request input is invalid or malformed');
            }

            $request = $request->withParsedBody($data);
        }

        return $handler->handle($request);
    }
}
