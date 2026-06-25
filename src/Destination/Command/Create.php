<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Bus\TransactionalCommand;
use Junction\Api\DestinationType\DestinationType;

final class Create implements TransactionalCommand
{
    /**
     * @param array<string, mixed>            $config
     * @param array<int, array{name: string}> $events
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $config,
        public readonly string $status,
        public readonly array $events,
        public readonly DestinationType $type,
    ) {}
}
