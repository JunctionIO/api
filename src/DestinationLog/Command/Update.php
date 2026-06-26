<?php

namespace Junction\Api\DestinationLog\Command;

final class Update
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $attemptedAt,
        public readonly ?string $error = null
    ) {}
}
