<?php

namespace Junction\Api\Bus;

use Georgeff\Database\Contract\DatabaseManagerInterface;

final class TransactionMiddleware
{
    public function __construct(private readonly DatabaseManagerInterface $db) {}

    public function __invoke(object $command, callable $next): mixed
    {
        if ($command instanceof TransactionalCommand) {
            return $this->db->transaction(fn() => $next($command));
        }

        return $next($command);
    }
}
