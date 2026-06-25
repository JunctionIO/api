<?php

namespace Junction\Api\DestinationType\Repository;

use Meritum\Database\Repository;
use Meritum\Database\Support\Collection;
use Junction\Api\DestinationType\DestinationType;
use Meritum\Database\Exception\ModelNotFoundException;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

/**
 * @extends Repository<DestinationType>
 */
final class PostgresDestinationTypeRepository extends Repository implements DestinationTypeRepositoryInterface
{
    public function all(): Collection
    {
        $this->query()->orderBy('id', 'ASC');

        return $this->get();
    }

    public function getByIds(array $ids, array $columns = ['*']): Collection
    {
        $this->query($columns)->whereIn('id', $ids);

        return $this->get();
    }

    /**
     * @param string[] $columns
     */
    public function findById(string $id, array $columns = ['*']): DestinationType
    {
        $this->query($columns)->where('id', $id);

        $model = $this->first();

        if (null === $model) {
            throw new ModelNotFoundException("Record with primary key value of [{$id}] was not found");
        }

        return $model;
    }

    protected function getModelClass(): string
    {
        return DestinationType::class;
    }
}
