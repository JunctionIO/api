<?php

namespace Junction\Api\Http\Middleware\Destination;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\AbstractValidator;

final class UpdateValidator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        $rules = [
            'name'        => ['string'],
            'description' => ['nullable', 'string'],
            'status'      => ['string', 'in' => ['active', 'disabled']],
            'config'      => ['array'],
        ];

        $type = $request->getAttribute(DestinationType::class);

        if (null !== $type) {
            assert($type instanceof DestinationType);

            $rules = $rules + $type->getConfigSchemaValidationRules();
        }

        return $rules;
    }
}
