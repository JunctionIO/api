<?php

namespace Junction\Api\Destination\Command;

final class QueryFind
{
    public function __construct(public readonly string $id) {}
}
