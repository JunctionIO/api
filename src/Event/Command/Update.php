<?php

namespace Junction\Api\Event\Command;

final class Update
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $description
    ) {}
}
