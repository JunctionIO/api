<?php

namespace Junction\Api\DestinationType\Repository;

use Meritum\Database\Repository;
use Meritum\Database\Support\Collection;
use Junction\Api\DestinationType\DestinationType;
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

    protected function getModelClass(): string
    {
        return DestinationType::class;
    }
}
