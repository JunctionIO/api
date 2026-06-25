<?php

namespace Junction\Api\Event;

use Meritum\Database\Support\Collection;
use Meritum\Database\RepositoryInterface;
use Meritum\Database\Support\CursorPaginator;

/**
 * @extends RepositoryInterface<Event>
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CursorPaginator<Event>
     */
    public function all(int $perPage, ?string $cursor = null): CursorPaginator;

    /**
     * @param string[] $ids
     * @param string[] $columns
     *
     * @return Collection<Event>
     */
    public function getByIds(array $ids, array $columns = ['*']): Collection;

    /**
     * @param string[] $names
     * @param string[] $columns
     *
     * @return Collection<Event>
     */
    public function getByName(array $names, array $columns = ['*']): Collection;

    public function findByName(string $name): ?Event;

    /**
     * @param Event[] $models
     *
     * @return Collection<Event>
     */
    public function insertMany(array $models): Collection;
}
