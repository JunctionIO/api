<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Bus\TransactionalCommand;

final class Delete implements TransactionalCommand
{
    public function __construct(public readonly string $id) {}
}
