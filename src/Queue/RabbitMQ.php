<?php

namespace Junction\Api\Queue;

use JsonSerializable;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class RabbitMQ implements QueueInterface
{
    private ?AMQPChannel $channel = null;

    public function __construct(private readonly AMQPStreamConnection $connection) {}

    public function declare(string $queue): void
    {
        $this->channel()
             ->queue_declare(
                 queue: $queue,
                 durable: true,
                 exclusive: false,
                 auto_delete: false
             );
    }

    public function publish(string $queue, JsonSerializable $message): void
    {
        $amqpMessage = new AMQPMessage(
            json_encode($message, JSON_THROW_ON_ERROR),
            [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $this->channel()->basic_publish($amqpMessage, '', $queue);
    }

    private function channel(): AMQPChannel
    {
        return $this->channel ??= $this->connection->channel();
    }
}
