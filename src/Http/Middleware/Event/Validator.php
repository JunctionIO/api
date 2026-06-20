<?php

namespace Junction\Api\Http\Middleware\Event;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class Validator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        return [
            'description' => ['nullable', 'required', 'string'],
        ];
    }
}
