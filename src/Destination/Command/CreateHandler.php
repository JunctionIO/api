<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Event\Event;
use Georgeff\Bus\DispatcherInterface;
use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\Command\FindManyOrCreate;
use Junction\Api\Destination\DestinationRepositoryInterface;

final class CreateHandler
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo,
        private readonly DispatcherInterface $dispatcher
    ) {}

    public function __invoke(Create $command): Destination
    {
        $model                    = new Destination();
        $model->name              = $command->name;
        $model->description       = $command->description;
        $model->destinationTypeId = $command->type->id;
        $model->config            = $command->config;
        $model->status            = $command->status;

        $this->repo->save($model);

        $model->setDestinationType($command->type);

        /** @var Collection<Event> $events */
        $events = $this->dispatcher->dispatch(new FindManyOrCreate($command->events));

        /** @var string[] $eventIds */
        $eventIds = $events->keys();

        $this->repo->attachEvents($model->id, $eventIds);

        $model->setEvents($events);

        return $model;
    }
}
