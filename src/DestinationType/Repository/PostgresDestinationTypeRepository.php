<?php

namespace Junction\Api\DestinationType\Repository;

use Meritum\Database\Repository;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

/**
 * @extends Repository<DestinationType>
 */
final class PostgresDestinationTypeRepository extends Repository implements DestinationTypeRepositoryInterface
{
    protected function getModelClass(): string
    {
        return DestinationType::class;
    }
}
