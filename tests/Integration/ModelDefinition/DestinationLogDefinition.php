<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use DateTimeImmutable;
use Meritum\Database\Support\Uuid;
use Meritum\ModelFactory\Definition;
use Junction\Api\DestinationLog\DestinationLog;

final class DestinationLogDefinition extends Definition
{
    public function getModelClass(): string
    {
        return DestinationLog::class;
    }

    public function getDefinition(): array
    {
        return [
            'id'             => Uuid::v7(),
            'trace_id'       => Uuid::v4(),
            'event_log_id'   => Uuid::v7(),
            'destination_id' => Uuid::v7(),
            'status'         => 'pending',
            'attempted_at'   => new DateTimeImmutable(),
            'error'          => null,
        ];
    }
}
