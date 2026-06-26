<?php

namespace Junction\Api\Http\Middleware\DestinationType;

use Junction\Api\Queue\QueueInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationType\DestinationType;

final class DeclareQueue implements MiddlewareInterface
{
    public function __construct(private readonly QueueInterface $queue) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $model = $request->getAttribute('data');

        assert($model instanceof DestinationType);

        $this->queue->declare($model->queue);

        return $handler->handle($request);
    }
}
