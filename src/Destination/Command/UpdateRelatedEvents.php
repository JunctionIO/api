<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Bus\TransactionalCommand;

final class UpdateRelatedEvents implements TransactionalCommand
{
    /**
     * @param array<int, array{name: string}> $events
     */
    public function __construct(
        public readonly string $id,
        public readonly array $events
    ) {}
}
