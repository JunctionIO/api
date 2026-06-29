<?php

namespace Junction\Api\Relay\Command;

use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;

final class Relay
{
    /**
     * @param array<mixed>            $payload
     * @param Collection<Destination> $destinations
     */
    public function __construct(
        public readonly string $traceId,
        public readonly string $logId,
        public readonly array $payload,
        public readonly Collection $destinations
    ) {}
}
