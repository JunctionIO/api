<?php

namespace Junction\Api\DestinationType;

use Meritum\Database\Support\Collection;
use Meritum\Database\RepositoryInterface;

/**
 * @extends RepositoryInterface<DestinationType>
 */
interface DestinationTypeRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Collection<DestinationType>
     */
    public function all(): Collection;

    /**
     * @param string[] $ids
     * @param string[] $columns
     *
     * @return Collection<DestinationType>
     */
    public function getByIds(array $ids, array $columns = ['*']): Collection;

    /**
     * @param string[] $columns
     *
     * @throws \Meritum\Database\Exception\ModelNotFoundException
     */
    public function findById(string $id, array $columns = ['*']): DestinationType;
}
