<?php

namespace Junction\Api\Http\Middleware\Event;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Event\Command\Update as UpdateCommand;

final class Update implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');

        assert(is_string($id));

        /** @var array{description: string|null} $input */
        $input = $request->getParsedBody();

        $model = $this->dispatcher->dispatch(new UpdateCommand($id, $input['description']));

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
