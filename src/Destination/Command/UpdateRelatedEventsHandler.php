<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Event\Event;
use Georgeff\Bus\DispatcherInterface;
use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\Command\FindManyOrCreate;
use Junction\Api\Destination\DestinationRepositoryInterface;

final class UpdateRelatedEventsHandler
{
    public function __construct(
        private readonly DispatcherInterface $dispatcher,
        private readonly DestinationRepositoryInterface $repo
    ) {}

    public function __invoke(UpdateRelatedEvents $command): Destination
    {
        /** @var Destination */
        $model = $this->dispatcher->dispatch(new QueryFind($command->id, false));

        /** @var Collection<Event> */
        $events = $this->dispatcher->dispatch(new FindManyOrCreate($command->events));

        /** @var string[] */
        $eventIds = $events->keys();

        $this->repo->attachEvents($command->id, $eventIds, true);

        $model->setEvents($events);

        return $model;
    }
}
