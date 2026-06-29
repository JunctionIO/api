<?php

namespace Junction\Api\EventLog\Command;

use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;

final class CreateHandler
{
    public function __construct(
        private readonly EventLogRepositoryInterface $repo
    ) {}

    public function __invoke(Create $command): EventLog
    {
        $model             = new EventLog();
        $model->traceId    = $command->traceId;
        $model->eventId    = $command->eventId;
        $model->authId     = $command->authId;
        $model->sourceIp   = $command->sourceIp;
        $model->payload    = $command->payload;
        $model->receivedAt = new \DateTimeImmutable();

        $model->initCreatedAt();

        $this->repo->save($model);

        return $model;
    }
}
