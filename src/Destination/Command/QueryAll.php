<?php

namespace Junction\Api\Destination\Command;

final class QueryAll
{
    public function __construct(
        public readonly int $limit,
        public readonly ?string $cursor = null
    ) {}
}
