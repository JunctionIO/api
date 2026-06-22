<?php

namespace Junction\Api\EventLog\Command;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLogRepositoryInterface;

final class QueryAllHandler
{
    public function __construct(
        private readonly EventLogRepositoryInterface $repo,
        private readonly EventRepositoryInterface $eventRepo
    ) {}

    /**
     * @return CursorPaginator<EventLog>
     */
    public function __invoke(QueryAll $command): CursorPaginator
    {
        if (null === $command->eventIds) { // no filter passed
            $cursor = $this->repo->all($command->limit, $command->cursor);
        } elseif ([] === $command->eventIds) { // filter passed but yielded no events
            $cursor = new CursorPaginator(new Collection([]), null, null, $command->limit);
        } else {
            $cursor = $this->repo->allForEvents($command->eventIds, $command->limit, $command->cursor);
        }

        $collection = $cursor->collection();

        if ($collection->isNotEmpty()) {
            $events = $this->getRelatedEvents($collection);

            $collection->each(function (EventLog $m) use ($events) {
                $event = $events->get($m->eventId);

                assert(null !== $event);

                $m->setEvent($event);
            });
        }

        return $cursor;
    }

    /**
     * @param Collection<EventLog> $logs
     *
     * @return Collection<Event>
     */
    private function getRelatedEvents(Collection $logs): Collection
    {
        $eventIds = [];

        foreach ($logs as $log) {
            $eventIds[] = $log->eventId;
        }

        return $this->eventRepo->getByIds($eventIds, ['id', 'name']);
    }
}
