<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\Command\UpdateRelatedEvents;

final class UpdateEvents implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        /** @var array{events: array<int, array{name: string}>} */
        $input = $request->getParsedBody();

        $model = $this->dispatcher->dispatch(new UpdateRelatedEvents($id, $input['events']));

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
