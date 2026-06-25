<?php

namespace Junction\Api\DestinationLog\Command;

final class QueryAllForDestination
{
    public function __construct(
        public readonly string $destinationId,
        public readonly int $limit,
        public readonly ?string $cursor = null
    ) {}
}
