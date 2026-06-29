<?php

namespace Junction\Api\Http\Middleware;

use Junction\Api\ApiToken\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\ApiToken\DecoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Exception\UnauthorizedHttpException;

final class ValidateApiToken implements MiddlewareInterface
{
    public function __construct(
        private readonly DecoderInterface $decoder,
        private readonly string $type
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = $request->getHeaderLine('X-Junction-Token');

        if ('' === $jwt) {
            throw new UnauthorizedHttpException($request, 'API token not provided');
        }

        $token = $this->decoder->decode($jwt);

        if (false === $token->isType($this->type)) {
            throw new UnauthorizedHttpException($request, "Invalid API token type. Type must be [{$this->type}]");
        }

        $request = $request->withAttribute(Token::class, $token);

        return $handler->handle($request);
    }
}
