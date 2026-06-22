<?php

namespace Junction\Api\EventLog;

use Meritum\Database\RepositoryInterface;
use Meritum\Database\Support\CursorPaginator;

/**
 * @extends RepositoryInterface<EventLog>
 */
interface EventLogRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CursorPaginator<EventLog>
     */
    public function all(int $perPage, ?string $cursor = null): CursorPaginator;

    /**
     * @param string[] $eventIds
     *
     * @return CursorPaginator<EventLog>
     */
    public function allForEvents(array $eventIds, int $perPage, ?string $cursor = null): CursorPaginator;
}
