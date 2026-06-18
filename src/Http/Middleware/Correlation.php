<?php

namespace Junction\Api\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Meritum\StructuredLogging\CorrelationId;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Correlation implements MiddlewareInterface
{
    private const string HEADER = 'X-Correlation-ID';

    public function __construct(private readonly CorrelationId $id) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine(self::HEADER);

        if ('' !== $header) {
            $this->id->set($header);
        }

        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER, (string) $this->id);
    }
}
