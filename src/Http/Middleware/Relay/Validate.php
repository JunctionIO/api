<?php

namespace Junction\Api\Http\Middleware\Relay;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class Validate extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        return ['payload' => ['required', 'array']];
    }
}
