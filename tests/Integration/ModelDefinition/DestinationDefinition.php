<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use Meritum\Database\Support\Uuid;
use Meritum\ModelFactory\Definition;
use Junction\Api\Destination\Destination;

final class DestinationDefinition extends Definition
{
    public function getModelClass(): string
    {
        return Destination::class;
    }

    public function getDefinition(): array
    {
        return [
            'id'                  => Uuid::v7(),
            'name'                => $this->faker->word,
            'description'         => $this->faker->sentence,
            'destination_type_id' => Uuid::v7(),
            'config'              => [],
            'status'              => 'active',
        ];
    }
}
