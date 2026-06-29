<?php

namespace Junction\Api\ApiToken;

final class Token
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly int $issuedAt
    ) {}

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }
}
