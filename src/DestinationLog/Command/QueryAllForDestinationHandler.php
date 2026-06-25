<?php

namespace Junction\Api\DestinationLog\Command;

use Meritum\Database\Support\CursorPaginator;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;

final class QueryAllForDestinationHandler
{
    public function __construct(
        private readonly DestinationLogRepositoryInterface $repo,
        private readonly EventLogRepositoryInterface $eventLogs,
        private readonly DestinationRepositoryInterface $destinations
    ) {}

    /**
     * @return CursorPaginator<DestinationLog>
     */
    public function __invoke(QueryAllForDestination $command): CursorPaginator
    {
        $destination = $this->destinations->findOrFail($command->destinationId);

        $cursor = $this->repo->getByDestination($destination->id, $command->limit, $command->cursor);

        $collection = $cursor->collection();

        $eventLogIds = [];

        $collection->each(function (DestinationLog $model) use (&$eventLogIds) {
            $eventLogIds[$model->eventLogId] = true;
        });

        $eventLogs = $this->eventLogs->getByIds(array_keys($eventLogIds), ['id', 'payload', 'source_ip', 'received_at']);

        $collection->each(function (DestinationLog $model) use ($eventLogs, $destination) {
            $eventLog = $eventLogs->get($model->eventLogId);

            assert(null !== $eventLog);

            $model->setEventLog($eventLog);
            $model->setDestination($destination);
        });

        return $cursor;
    }
}
