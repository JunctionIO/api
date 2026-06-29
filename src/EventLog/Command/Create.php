<?php

namespace Junction\Api\EventLog\Command;

final class Create
{
    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        public readonly string $traceId,
        public readonly string $eventId,
        public readonly string $authId,
        public readonly ?string $sourceIp,
        public readonly array $payload,
    ) {}
}
