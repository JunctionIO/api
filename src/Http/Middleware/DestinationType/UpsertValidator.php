<?php

namespace Junction\Api\Http\Middleware\DestinationType;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class UpsertValidator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        return [
            'name'                     => ['required', 'string'],
            'description'              => ['nullable', 'string'],
            'queue'                    => ['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']],
            'config_schema'            => ['required', 'config_schema'],
        ];
    }
}
