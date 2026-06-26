<?php

namespace Junction\Api\Queue;

use JsonSerializable;

interface QueueInterface
{
    public function declare(string $queue): void;

    public function publish(string $queue, JsonSerializable $message): void;
}
