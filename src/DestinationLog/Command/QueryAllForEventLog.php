<?php

namespace Junction\Api\DestinationLog\Command;

final class QueryAllForEventLog
{
    public function __construct(
        public readonly string $eventLogId,
        public readonly int $limit,
        public readonly ?string $cursor = null
    ) {}
}
