<?php

namespace Junction\Api\EventLog\Command;

final class QueryAll
{
    /**
     * @param null|string[] $eventIds
     */
    public function __construct(
        public readonly int $limit,
        public readonly ?string $cursor = null,
        public readonly ?array $eventIds = null
    ) {}
}
