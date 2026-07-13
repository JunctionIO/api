<?php

namespace Junction\Api\Test\Integration\Queue;

use JsonSerializable;
use Junction\Api\Queue\QueueInterface;

final class MemoryQueue implements QueueInterface
{
    /**
     * @var array<string, JsonSerializable[]>
     */
    private array $queues = [];

    public function declare(string $queue): void
    {
        $this->queues[$queue] = [];
    }

    public function publish(string $queue, JsonSerializable $message): void
    {
        if (false === $this->has($queue)) {
            $this->declare($queue);
        }

        $this->queues[$queue][] = $message;
    }

    public function has(string $queue): bool
    {
        return isset($this->queues[$queue]);
    }

    /**
     * @return JsonSerializable[]
     */
    public function getMessages(string $queue): array
    {
        if (false === $this->has($queue)) {
            return [];
        }

        $messages = $this->queues[$queue];

        $this->queues[$queue] = [];

        return $messages;
    }

    public function flush(): void
    {
        $this->queues = [];
    }

    /**
     * @return array<string, JsonSerializable[]>
     */
    public function getQueues(): array
    {
        return $this->queues;
    }
}
