<?php

namespace Junction\Api\Http\Handler;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Serialization\FormatterInterface;
use Meritum\Serialization\Resource\ResourceInterface;

final class JsonResponseHandler implements RequestHandlerInterface
{
    public function __construct(private readonly FormatterInterface $formatter) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $status = $request->getAttribute('status', 200);

        $resource = $request->getAttribute(ResourceInterface::class);

        assert(is_int($status));
        assert($resource instanceof ResourceInterface);

        $envelope = $this->formatter->format($resource);

        return new JsonResponse($envelope, $status);
    }
}
