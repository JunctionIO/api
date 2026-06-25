<?php

namespace Junction\Api\DestinationLog;

use Meritum\Database\RepositoryInterface;
use Meritum\Database\Support\CursorPaginator;

/**
 * @extends RepositoryInterface<DestinationLog>
 */
interface DestinationLogRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CursorPaginator<DestinationLog>
     */
    public function getByEventLog(string $eventLogId, int $perPage, ?string $cursor = null): CursorPaginator;

    /**
     * @return CursorPaginator<DestinationLog>
     */
    public function getByDestination(string $destinationId, int $perPage, ?string $cursor = null): CursorPaginator;
}
