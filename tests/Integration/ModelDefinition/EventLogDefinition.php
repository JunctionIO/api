<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use DateTimeImmutable;
use Meritum\Database\Support\Uuid;
use Junction\Api\EventLog\EventLog;
use Meritum\ModelFactory\Definition;

final class EventLogDefinition extends Definition
{
    public function getModelClass(): string
    {
        return EventLog::class;
    }

    public function getDefinition(): array
    {
        return [
            'id'          => Uuid::v7(),
            'trace_id'    => Uuid::v4(),
            'event_id'    => Uuid::v7(),
            'payload'     => [],
            'source_ip'   => $this->faker->ipv4,
            'auth_id'     => Uuid::v4(),
            'received_at' => new DateTimeImmutable(),
            'created_at'  => new DateTimeImmutable(),
        ];
    }
}
