<?php

namespace Junction\Api\Destination\Command;

final class QueryActiveByEvent
{
    public function __construct(public readonly string $eventId) {}
}
