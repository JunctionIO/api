<?php

namespace Junction\Api\EventLog\Command;

use Junction\Api\EventLog\EventLog;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLogRepositoryInterface;

final class QueryFindHandler
{
    public function __construct(
        private readonly EventLogRepositoryInterface $repo,
        private readonly EventRepositoryInterface $eventRepo
    ) {}

    public function __invoke(QueryFind $command): EventLog
    {
        $model = $this->repo->findOrFail($command->id);

        $event = $this->eventRepo->find($model->eventId);

        assert(null !== $event);

        $model->setEvent($event);

        return $model;
    }
}
