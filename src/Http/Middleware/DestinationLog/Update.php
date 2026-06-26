<?php

namespace Junction\Api\Http\Middleware\DestinationLog;

use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationLog\Command\Update as UpdateCommand;

final class Update implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var array{
         *      log_id: string,
         *      status: string,
         *      attempted_at: string,
         *      error?: string|null
         * }
         */
        $input = $request->getParsedBody();

        $data = $this->dispatcher->dispatch(new UpdateCommand(
            $input['log_id'],
            $input['status'],
            $input['attempted_at'],
            $input['error'] ?? null
        ));

        $request = $request->withAttribute('data', $data);

        return $handler->handle($request);
    }
}
