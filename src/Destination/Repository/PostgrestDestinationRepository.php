<?php

namespace Junction\Api\Destination\Repository;

use Meritum\Database\Repository;
use Meritum\Database\Support\Uuid;
use Junction\Api\Destination\Destination;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Destination\DestinationRepositoryInterface;

/**
 * @extends Repository<Destination>
 */
final class PostgrestDestinationRepository extends Repository implements DestinationRepositoryInterface
{
    public function all(int $perPage, ?string $cursor = null): CursorPaginator
    {
        $this->query();

        return $this->cursor($perPage, $cursor);
    }

    public function getEventIds(string $id): array
    {
        $query = $this->db
                      ->select(['event_id'])
                      ->from('destination_events')
                      ->where('destination_id', $id);

        /** @var array<int, array{event_id: string}> $results */
        $results = $this->db->fetchAll($query);

        $ids = [];

        foreach ($results as $row) {
            $ids[] = $row['event_id'];
        }

        return $ids;
    }

    public function getEventIdsForMany(array $ids): array
    {
        $query = $this->db
                      ->select(['event_id', 'destination_id'])
                      ->from('destination_events')
                      ->whereIn('destination_id', $ids);

        /** @var array<int, array{event_id: string, destination_id: string}> */
        return $this->db->fetchAll($query);
    }

    public function attachEvents(string $id, array $eventIds, bool $replace = false): int
    {
        if ($replace) {
            $this->clearEvents($id);
        }

        $query = $this->db->insert()->into('destination_events');

        foreach ($eventIds as $eventId) {
            $query->addRow(['destination_id' => $id, 'event_id' => $eventId]);
        }

        return $this->db->fetchAffected($query);
    }

    public function clearEvents(string $id): int
    {
        $query = $this->db
                      ->delete()
                      ->from('destination_events')
                      ->where('destination_id', $id);

        return $this->db->fetchAffected($query);
    }

    protected function getModelClass(): string
    {
        return Destination::class;
    }

    protected function generateUuid(): string
    {
        return Uuid::v7();
    }
}
