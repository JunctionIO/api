<?php

namespace Junction\Api\Support;

final class CursorParams
{
    public readonly int $limit;

    public readonly ?string $cursor;

    /**
     * @param array<string, string> $query
     */
    public function __construct(array $query, int $limitDefault = 25, int $limitMax = 100)
    {
        $limit = $query['limit'] ?? $limitDefault;

        $this->cursor = $query['cursor'] ?? null;
        $this->limit  = max(1, min((int) $limit, $limitMax));
    }
}
