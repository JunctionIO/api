<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Destination\Command\Create as CreateCommand;

final class Create implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var array{
         *      name: string,
         *      description?: string|null,
         *      status: string,
         *      events: array<int, array{name: string}>,
         *      config: array<string, mixed>
         * } $input
         */
        $input = $request->getParsedBody();

        $type = $request->getAttribute(DestinationType::class);

        assert($type instanceof DestinationType);

        $model = $this->dispatcher->dispatch(new CreateCommand(
            $input['name'],
            $input['description'] ?? null,
            $input['config'],
            $input['status'],
            $input['events'],
            $type
        ));

        $request = $request->withAttribute('data', $model);

        return $handler->handle($request);
    }
}
