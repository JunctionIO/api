<?php

namespace Junction\Api\Destination\Command;

use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class UpdateHandler
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo,
        private readonly DestinationTypeRepositoryInterface $types,
        private readonly EventRepositoryInterface $events
    ) {}

    public function __invoke(Update $command): Destination
    {
        $model = $command->model;
        $data  = $command->data;

        $this->setDestinationType($model);

        $this->setEvents($model);

        if ([] === $data) {
            return $model;
        }

        if (array_key_exists('name', $data)) {
            $model->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $model->description = $data['description'];
        }

        if (array_key_exists('config', $data)) {
            $model->config = $data['config'];
        }

        if (array_key_exists('status', $data)) {
            $model->status = $data['status'];
        }

        $this->repo->save($model);

        return $model;
    }

    private function setDestinationType(Destination $model): void
    {
        $type = $this->types->findById($model->destinationTypeId, ['id', 'name']);

        $model->setDestinationType($type);
    }

    private function setEvents(Destination $model): void
    {
        $eventIds = $this->repo->getEventIds($model->id);

        $events = [] !== $eventIds
            ? $this->events->getByIds($eventIds, ['id', 'name'])
            : new Collection([]);

        $model->setEvents($events);
    }
}
