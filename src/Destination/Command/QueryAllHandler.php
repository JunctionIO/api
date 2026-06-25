<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class QueryAllHandler
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo,
        private readonly DestinationTypeRepositoryInterface $types,
        private readonly EventRepositoryInterface $events
    ) {}

    /**
     * @return CursorPaginator<Destination>
     */
    public function __invoke(QueryAll $command): CursorPaginator
    {
        $cursor = $this->repo->all($command->limit, $command->cursor);

        $this->loadRelatedDestinationTypes($cursor->collection());

        $this->loadRelatedEvents($cursor->collection());

        return $cursor;
    }

    /**
     * @param Collection<Destination> $destinations
     */
    private function loadRelatedDestinationTypes(Collection $destinations): void
    {
        if ($destinations->isEmpty()) {
            return;
        }

        $typeIds = [];

        $destinations->each(function (Destination $m) use (&$typeIds) {
            $typeIds[$m->destinationTypeId] = true;
        });

        $types = $this->types->getByIds(array_keys($typeIds), ['id', 'name']);

        $destinations->each(function (Destination $m) use ($types) {
            $t = $types->get($m->destinationTypeId);

            assert(null !== $t);

            $m->setDestinationType($t);
        });
    }

    /**
     * @param Collection<Destination> $destinations
     */
    private function loadRelatedEvents(Collection $destinations): void
    {
        if ($destinations->isEmpty()) {
            return;
        }

        /** @var string[] $destinationIds */
        $destinationIds = $destinations->keys();

        $results = $this->repo->getEventIdsForMany($destinationIds);

        $eventIds      = [];
        $byDestination = [];

        foreach ($results as $row) {
            $eventIds[$row['event_id']] = true;

            $byDestination[$row['destination_id']][] = $row['event_id'];
        }

        /** @var Collection<Event> $events */
        $events = [] !== $eventIds
            ? $this->events->getByIds(array_keys($eventIds), ['id', 'name'])
            : new Collection([]);

        $destinations->each(function (Destination $m) use ($byDestination, $events) {
            /** @var array<string, Event> */
            $subset = [];

            foreach ($byDestination[$m->id] ?? [] as $eventId) {
                $event = $events->get($eventId);

                if (null !== $event) {
                    $subset[$event->id] = $event;
                }
            }

            $m->setEvents(new Collection($subset));
        });
    }
}
