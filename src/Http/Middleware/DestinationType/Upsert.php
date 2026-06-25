<?php

namespace Junction\Api\Http\Middleware\DestinationType;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationType\Command\Upsert as UpsertCommand;

final class Upsert implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var array{
         *      name: string,
         *      description?: string|null,
         *      queue: string,
         *      config_schema: array<string, array{required: bool, rules: array<int, string|array<string, mixed>>}>
         * }
         */
        $input = $request->getParsedBody();

        $data = $this->dispatcher->dispatch(new UpsertCommand(
            $input['name'],
            $input['queue'],
            $input['description'] ?? null,
            $input['config_schema']
        ));

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
