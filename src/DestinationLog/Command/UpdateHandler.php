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

        $attemptedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $command->attemptedAt);

        if (false === $attemptedAt) {
            throw new \InvalidArgumentException("Invalid attempted_at format: {$command->attemptedAt}");
        }

        $model->status      = $command->status;
        $model->attemptedAt = $attemptedAt;
        $model->error       = $command->error;

        $this->repo->save($model);

        return $model;
    }
}
