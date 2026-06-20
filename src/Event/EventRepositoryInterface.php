<?php

namespace Junction\Api\Event;

use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use Meritum\Database\RepositoryInterface;
use Meritum\Database\Support\CursorPaginator;

/**
 * @extends RepositoryInterface<Event>
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    public function exists(string $id): bool;

    /**
     * @return CursorPaginator<Event>
     */
    public function all(int $perPage, ?string $cursor = null): CursorPaginator;

    /**
     * @param string[] $ids
     *
     * @return Collection<Event>
     */
    public function getByIds(array $ids): Collection;

    public function findByName(string $name): ?Event;
}
