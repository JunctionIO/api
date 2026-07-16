<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use Junction\Api\Event\Event;
use Meritum\Database\Support\Uuid;
use Meritum\ModelFactory\Definition;

final class EventDefinition extends Definition
{
    public function getModelClass(): string
    {
        return Event::class;
    }

    public function getDefinition(): array
    {
        return [
            'id'          => Uuid::v7(),
            'name'        => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
        ];
    }
}
