<?php

namespace Junction\Api\Support;

use Meritum\Database\Support\CursorPaginator;
use Meritum\Serialization\Pagination\CursorInterface;

final class CursorPaginatorAdaptor implements CursorInterface
{
    /**
     * @param CursorPaginator<\Meritum\Database\Model> $cursor
     */
    public function __construct(private readonly CursorPaginator $cursor) {}

    public function getPrevious(): ?string
    {
        return $this->cursor->previousCursor();
    }

    public function getNext(): ?string
    {
        return $this->cursor->nextCursor();
    }

    public function getPerPage(): int
    {
        return $this->cursor->perPage();
    }
}
