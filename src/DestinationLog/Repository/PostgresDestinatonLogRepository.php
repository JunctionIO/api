<?php

namespace Junction\Api\DestinationLog\Repository;

use Meritum\Database\Repository;
use Meritum\Database\Support\Uuid;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;

/**
 * @extends Repository<DestinationLog>
 */
final class PostgresDestinatonLogRepository extends Repository implements DestinationLogRepositoryInterface
{
    public function getByEventLog(string $eventLogId, int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query()->where('event_log_id', $eventLogId);

        return $this->cursor($perPage, $cursor);
    }

    public function getByDestination(string $destinationId, int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query()->where('destination_id', $destinationId);

        return $this->cursor($perPage, $cursor);
    }

    public function insertMany(array $models): Collection
    {
        if ([] === $models) {
            throw new \LogicException('Models array cannot be empty');
        }

        $query = $this->db->insert()->into($models[0]->getTable());

        $collect = [];

        foreach ($models as $model) {
            $model->setPrimaryKeyValue($this->generateUuid());

            $model->touchTimestamps();

            $query->addRow($model->getDirty());

            $collect[$model->id] = $model;
        }

        $this->db->fetchAffected($query);

        return new Collection($collect);
    }

    protected function getModelClass(): string
    {
        return DestinationLog::class;
    }

    protected function generateUuid(): string
    {
        return Uuid::v7();
    }
}
