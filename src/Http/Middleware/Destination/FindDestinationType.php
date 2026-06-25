<?php

namespace Junction\Api\Http\Middleware\Destination;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class FindDestinationType implements MiddlewareInterface
{
    public function __construct(private readonly DestinationTypeRepositoryInterface $repo) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $input = $request->getParsedBody();

        assert(is_array($input));

        if (null !== ($input['destination_type_id'] ?? null)) {
            assert(is_string($input['destination_type_id']));

            $model = $this->repo->findOrFail($input['destination_type_id']);

            $request = $request->withAttribute(DestinationType::class, $model);
        }

        return $handler->handle($request);
    }
}
