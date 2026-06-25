<?php

namespace Junction\Api\Http\Middleware\Destination;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\AbstractValidator;

final class CreateValidator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        $data = [
            'name'                => ['required', 'string'],
            'description'         => ['nullable', 'string'],
            'destination_type_id' => ['required', 'string'],
            'status'              => ['required', 'string', 'in' => ['active', 'disabled']],
            'events'              => ['required', 'array'],
            'events.*.name'       => ['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']],
            'config'              => ['required', 'array'],
        ];

        $type = $request->getAttribute(DestinationType::class);

        if (null !== $type) {
            assert($type instanceof DestinationType);

            $data = $data + $type->getConfigSchemaValidationRules();
        }

        return $data;
    }
}
