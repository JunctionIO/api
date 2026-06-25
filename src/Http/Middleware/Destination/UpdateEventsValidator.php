<?php

namespace Junction\Api\Http\Middleware\Destination;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class UpdateEventsValidator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        return [
            'events'        => ['required', 'array'],
            'events.*.name' => ['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']],
        ];
    }
}
