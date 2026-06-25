<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Destination\DestinationRepositoryInterface;

final class DeleteHandler
{
    public function __construct(private readonly DestinationRepositoryInterface $repo) {}

    public function __invoke(Delete $command): void
    {
        $model = $this->repo->findOrFail($command->id);

        $this->repo->clearEvents($model->id);

        $this->repo->delete($model);
    }
}
