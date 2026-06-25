<?php

namespace Junction\Api\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\Destination\Destination;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\Command\Update as UpdateCommand;

final class Update implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $model = $request->getAttribute(Destination::class);

        assert($model instanceof Destination);

        /**
         * @var array{
         *      name?: string,
         *      description?: string|null,
         *      status?: string,
         *      config?: array<string, mixed>
         * }
         */
        $input = $request->getParsedBody();

        $request = $request->withAttribute('data', $this->dispatcher->dispatch(new UpdateCommand($model, $input)));

        return $handler->handle($request);
    }
}
