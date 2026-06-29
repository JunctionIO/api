<?php

namespace Junction\Api\Relay\Command;

final class QueryEvent
{
    public function __construct(public readonly string $name) {}
}
