<?php

namespace Junction\Api\Test\Support;

use PHPUnit\Framework\TestCase;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Support\CursorPaginatorAdaptor;

final class CursorPaginatorAdaptorTest extends TestCase
{
    public function test_get_next_returns_next_cursor(): void
    {
        $paginator = new CursorPaginator(new Collection([]), 'next-token', null, 15);

        $this->assertSame('next-token', (new CursorPaginatorAdaptor($paginator))->getNext());
    }

    public function test_get_next_returns_null_when_no_next(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 15);

        $this->assertNull((new CursorPaginatorAdaptor($paginator))->getNext());
    }

    public function test_get_previous_returns_previous_cursor(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, 'prev-token', 15);

        $this->assertSame('prev-token', (new CursorPaginatorAdaptor($paginator))->getPrevious());
    }

    public function test_get_previous_returns_null_when_no_previous(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 15);

        $this->assertNull((new CursorPaginatorAdaptor($paginator))->getPrevious());
    }

    public function test_get_per_page_returns_per_page(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 15);

        $this->assertSame(15, (new CursorPaginatorAdaptor($paginator))->getPerPage());
    }
}
