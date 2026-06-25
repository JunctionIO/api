<?php

namespace Junction\Api\Http\Middleware\Destination;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\Destination\Destination;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;

final class FindForUpdate implements MiddlewareInterface
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id', '');

        assert(is_string($id));

        $model = $this->repo->findOrFail($id);

        $request = $request->withAttribute(Destination::class, $model);

        $input = $request->getParsedBody();

        assert(is_array($input));

        if (null !== ($input['config'] ?? null)) {
            $input['destination_type_id'] = $model->destinationTypeId;

            $request = $request->withParsedBody($input);
        }

        return $handler->handle($request);
    }
}
