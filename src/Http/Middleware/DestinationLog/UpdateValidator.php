<?php

namespace Junction\Api\Http\Middleware\DestinationLog;

use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class UpdateValidator extends AbstractValidator
{
    public function rules(ServerRequestInterface $request): array
    {
        return [
            'log_id'       => ['required', 'string'],
            'status'       => ['required', 'string', 'in' => ['dispatched', 'errored']],
            'attempted_at' => ['required', 'string', 'dateFormat' => ['Y-m-d\TH:i:sp']],
            'error'        => ['nullable', 'string'],
        ];
    }
}
