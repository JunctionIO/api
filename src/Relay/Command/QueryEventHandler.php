<?php

namespace Junction\Api\Relay\Command;

use Junction\Api\Event\Event;
use Georgeff\Bus\DispatcherInterface;
use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\Event\Command\FindOrCreate;
use Junction\Api\Destination\Command\QueryActiveByEvent;

final class QueryEventHandler
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function __invoke(QueryEvent $command): Event
    {
        /** @var Event */
        $event = $this->dispatcher->dispatch(new FindOrCreate($command->name));

        /** @var Collection<Destination> */
        $destinations = $this->dispatcher->dispatch(new QueryActiveByEvent($event->id));

        $event->setDestinations($destinations);

        return $event;
    }
}
