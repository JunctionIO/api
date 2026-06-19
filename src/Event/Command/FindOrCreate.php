<?php

namespace Junction\Api\Event\Command;

final class FindOrCreate
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $description = null
    ) {}
}
