<?php

namespace Junction\Api\Event\Command;

final class FindManyOrCreate
{
    /**
     * @param array<int, array{name: string}> $events
     */
    public function __construct(public readonly array $events) {}
}
