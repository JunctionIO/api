<?php

namespace Junction\Api\Test\Integration\Logging;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;

final class LogSpy implements LoggerInterface
{
    use LoggerTrait;

    /**
     * array{level: string, message: string|\Stringable, context: array<mixed>}
     */
    private array $logs = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
