<?php

namespace Junction\Api\DestinationType\Command;

final class Upsert
{
    /**
     * @param array<string, array{required: bool, rules: array<int, string|array<string, mixed>>}> $configSchema
     */
    public function __construct(
        public readonly string $name,
        public readonly string $queue,
        public readonly ?string $description = null,
        public readonly array $configSchema = []
    ) {}
}
