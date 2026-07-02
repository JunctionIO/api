<?php

namespace Junction\Api\DestinationLog\Command;

use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;

final class UpdateHandler
{
    public function __construct(
        private readonly DestinationLogRepositoryInterface $repo
    ) {}

    public function __invoke(Update $command): DestinationLog
    {
        $model = $this->repo->findOrFail($command->id);

        $model->status      = $command->status;
        $model->attemptedAt = new \DateTimeImmutable($command->attemptedAt);
        $model->error       = $command->error;

        $this->repo->save($model);

        return $model;
    }
}
