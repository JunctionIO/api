<?php

namespace Junction\Api\EventLog\Command;

final class QueryFind
{
    public function __construct(public readonly string $id) {}
}
