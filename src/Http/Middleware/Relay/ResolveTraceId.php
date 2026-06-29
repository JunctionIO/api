<?php

namespace Junction\Api\Http\Middleware\Relay;

use Junction\Api\Trace\TraceId;
use Meritum\Validation\Rule\Uuid;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResolveTraceId implements MiddlewareInterface
{
    private const string HEADER = 'X-Junction-Trace-ID';

    public function __construct(private readonly TraceId $id, private readonly Uuid $uuid) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getHeaderLine(self::HEADER);

        if ($this->uuid->validate($id)) {
            $this->id->set($id);
        }

        $request = $request->withAttribute(TraceId::class, (string) $this->id);

        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER, (string) $this->id);
    }
}
