<?php

namespace Junction\Api\Destination;

use Meritum\Database\Support\Collection;
use Meritum\Database\RepositoryInterface;
use Meritum\Database\Support\CursorPaginator;

/**
 * @extends RepositoryInterface<Destination>
 */
interface DestinationRepositoryInterface extends RepositoryInterface
{
    /**
     * @return CursorPaginator<Destination>
     */
    public function all(int $perPage, ?string $cursor = null): CursorPaginator;

    /**
     * @param string[] $ids
     * @param string[] $columns
     *
     * @return Collection<Destination>
     */
    public function getByIds(array $ids, array $columns = ['*']): Collection;

    /**
     * @param string[] $columns
     *
     * @return Collection<Destination>
     */
    public function getActiveByEvent(string $eventId, array $columns = ['*']): Collection;

    /**
     * @return string[]
     */
    public function getEventIds(string $id): array;

    /**
     * @param string[] $ids
     *
     * @return array<int, array{event_id: string, destination_id: string}>
     */
    public function getEventIdsForMany(array $ids): array;

    /**
     * @param string[] $eventIds
     */
    public function attachEvents(string $id, array $eventIds, bool $replace = false): int;

    public function clearEvents(string $id): int;
}
