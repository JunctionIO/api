<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use Meritum\Database\Support\Uuid;
use Meritum\ModelFactory\Definition;
use Junction\Api\DestinationType\DestinationType;

final class DestinationTypeDefinition extends Definition
{
    public function getModelClass(): string
    {
        return DestinationType::class;
    }

    public function getDefinition(): array
    {
        return [
            'id'            => Uuid::v7(),
            'name'          => $this->faker->unique()->word,
            'queue'         => $this->faker->unique()->slug(1),
            'description'   => $this->faker->sentence,
            'config_schema' => [],
        ];
    }
}
