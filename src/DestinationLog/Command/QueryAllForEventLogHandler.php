<?php

namespace Junction\Api\DestinationLog\Command;

use Meritum\Database\Support\CursorPaginator;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;

final class QueryAllForEventLogHandler
{
    public function __construct(
        private readonly DestinationLogRepositoryInterface $repo,
        private readonly EventLogRepositoryInterface $eventLogs,
        private readonly DestinationRepositoryInterface $destinations
    ) {}

    /**
     * @return CursorPaginator<DestinationLog>
     */
    public function __invoke(QueryAllForEventLog $command): CursorPaginator
    {
        $eventLog = $this->eventLogs->findOrFail($command->eventLogId);

        $cursor = $this->repo->getByEventLog($eventLog->id, $command->limit, $command->cursor);

        $destinationIds = [];

        $collection = $cursor->collection();

        $collection->each(function (DestinationLog $model) use (&$destinationIds) {
            $destinationIds[$model->destinationId] = true;
        });

        $destinations = $this->destinations->getByIds(array_keys($destinationIds), ['id', 'name', 'description']);

        $collection->each(function (DestinationLog $model) use ($eventLog, $destinations) {
            $destination = $destinations->get($model->destinationId);

            assert(null !== $destination);

            $model->setEventLog($eventLog);
            $model->setDestination($destination);
        });

        return $cursor;
    }
}
