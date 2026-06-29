<?php

namespace Junction\Api\Destination\Command;

use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class QueryActiveByEventHandler
{
    public function __construct(
        private readonly DestinationRepositoryInterface $repo,
        private readonly DestinationTypeRepositoryInterface $types
    ) {}

    /**
     * @return Collection<Destination>
     */
    public function __invoke(QueryActiveByEvent $command): Collection
    {
        $collection = $this->repo->getActiveByEvent($command->eventId);

        $typeIds = [];

        $collection->each(function (Destination $model) use (&$typeIds) {
            $typeIds[$model->destinationTypeId] = true;
        });

        $types = $this->types->getByIds(array_keys($typeIds), ['id', 'name', 'queue']);

        $collection->each(function (Destination $model) use ($types) {
            $type = $types->get($model->destinationTypeId);

            assert($type instanceof DestinationType);

            $model->setDestinationType($type);
        });

        return $collection;
    }
}
