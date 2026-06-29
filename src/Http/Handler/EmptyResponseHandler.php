<?php

namespace Junction\Api\Http\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\EmptyResponse;

final class EmptyResponseHandler implements RequestHandlerInterface
{
    public function __construct(private readonly int $status = 204) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse($this->status);
    }
}
