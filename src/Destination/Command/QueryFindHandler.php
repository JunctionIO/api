<?php

namespace Junction\Api\Destination\Command;

use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class QueryFindHandler
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo,
        private readonly DestinationTypeRepositoryInterface $types,
        private readonly EventRepositoryInterface $events
    ) {}

    public function __invoke(QueryFind $command): Destination
    {
        $model = $this->repo->findOrFail($command->id);

        $type = $this->types->findById($model->destinationTypeId, ['id', 'name']);

        $model->setDestinationType($type);

        $eventIds = $this->repo->getEventIds($model->id);

        $events = [] !== $eventIds
            ? $this->events->getByIds($eventIds, ['id', 'name'])
            : new Collection([]);

        $model->setEvents($events);

        return $model;
    }
}
