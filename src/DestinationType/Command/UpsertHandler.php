<?php

namespace Junction\Api\DestinationType\Command;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class UpsertHandler
{
    public function __construct(
        private readonly DestinationTypeRepositoryInterface $repo
    ) {}

    public function __invoke(Upsert $command): DestinationType
    {
        $model = $this->repo->findBy('name', $command->name) ?? new DestinationType();

        $model->name         = $command->name;
        $model->description  = $command->description;
        $model->queue        = $command->queue;
        $model->configSchema = $command->configSchema;

        $this->repo->save($model);

        return $model;
    }
}
