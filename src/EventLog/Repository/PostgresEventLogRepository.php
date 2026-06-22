<?php

namespace Junction\Api\EventLog\Repository;

use Meritum\Database\Repository;
use Meritum\Database\Support\Uuid;
use Junction\Api\EventLog\EventLog;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\EventLog\EventLogRepositoryInterface;

/**
 * @extends Repository<EventLog>
 */
final class PostgresEventLogRepository extends Repository implements EventLogRepositoryInterface
{
    public function all(int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query();

        return $this->cursor($perPage, $cursor);
    }

    public function allForEvents(array $eventIds, int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query()->whereIn('event_id', $eventIds);

        return $this->cursor($perPage, $cursor);
    }

    protected function getModelClass(): string
    {
        return EventLog::class;
    }

    protected function generateUuid(): string
    {
        return Uuid::v7();
    }
}
