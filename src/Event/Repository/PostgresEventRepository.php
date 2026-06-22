<?php

namespace Junction\Api\Event\Repository;

use Junction\Api\Event\Event;
use Meritum\Database\Repository;
use Meritum\Database\Support\Uuid;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Event\EventRepositoryInterface;

/**
 * @extends Repository<Event>
 */
final class PostgresEventRepository extends Repository implements EventRepositoryInterface
{
    public function exists(string $id): bool
    {
        $this->query(['id'])->where('id', $id);

        $result = $this->first();

        return null !== $result;
    }

    public function all(int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query();

        return $this->cursor($perPage, $cursor);
    }

    public function getByIds(array $ids, array $columns = ['*']): Collection
    {
        $this->query($columns)->whereIn('id', $ids);

        return $this->get();
    }

    public function getByName(array $names, array $columns = ['*']): Collection
    {
        $this->query($columns)->whereIn('name', $names);

        return $this->get();
    }

    public function findByName(string $name): ?Event
    {
        return $this->findBy('name', $name);
    }

    protected function getModelClass(): string
    {
        return Event::class;
    }

    protected function generateUuid(): string
    {
        return Uuid::v7();
    }
}
