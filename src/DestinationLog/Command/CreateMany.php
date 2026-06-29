<?php

namespace Junction\Api\DestinationLog\Command;

final class CreateMany
{
    /**
     * @param string[] $destinationIds
     */
    public function __construct(
        public readonly string $traceId,
        public readonly string $eventLogId,
        public readonly array $destinationIds
    ) {}
}
