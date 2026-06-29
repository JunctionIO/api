<?php

namespace Junction\Api\DestinationLog\Command;

use Meritum\Database\Support\Collection;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;

final class CreateManyHandler
{
    public function __construct(
        private readonly DestinationLogRepositoryInterface $repo
    ) {}

    /**
     * @return Collection<DestinationLog>
     */
    public function __invoke(CreateMany $command): Collection
    {
        $models = [];

        foreach ($command->destinationIds as $id) {
            $m                = new DestinationLog();
            $m->traceId       = $command->traceId;
            $m->eventLogId    = $command->eventLogId;
            $m->destinationId = $id;
            $m->status        = 'pending';

            $models[] = $m;
        }

        return $this->repo->insertMany($models);
    }
}
