<?php

namespace Junction\Api\Test\Queue;

use JsonSerializable;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Junction\Api\Queue\RabbitMQ;
use PHPUnit\Framework\TestCase;

final class RabbitMQTest extends TestCase
{
    private function makeMessage(): JsonSerializable
    {
        return new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['event' => 'order.placed', 'id' => 'abc-123'];
            }
        };
    }

    private function makeChannel(): AMQPChannel
    {
        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('queue_declare')->willReturn(null);
        $channel->method('basic_publish')->willReturn(null);

        return $channel;
    }

    private function makeConnection(AMQPChannel $channel): AMQPStreamConnection
    {
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->method('channel')->willReturn($channel);

        return $connection;
    }

    // declare

    public function test_declare_passes_queue_name(): void
    {
        $capturedArgs = null;
        $channel      = $this->makeChannel();
        $channel->expects($this->once())
            ->method('queue_declare')
            ->willReturnCallback(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        (new RabbitMQ($this->makeConnection($channel)))->declare('my-queue');

        $this->assertSame('my-queue', $capturedArgs[0]);
    }

    public function test_declare_sets_durable(): void
    {
        $capturedArgs = null;
        $channel      = $this->makeChannel();
        $channel->expects($this->once())
            ->method('queue_declare')
            ->willReturnCallback(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        (new RabbitMQ($this->makeConnection($channel)))->declare('my-queue');

        $this->assertTrue($capturedArgs[2]); // durable
    }

    public function test_declare_is_not_exclusive(): void
    {
        $capturedArgs = null;
        $channel      = $this->makeChannel();
        $channel->expects($this->once())
            ->method('queue_declare')
            ->willReturnCallback(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        (new RabbitMQ($this->makeConnection($channel)))->declare('my-queue');

        $this->assertFalse($capturedArgs[3]); // exclusive
    }

    public function test_declare_does_not_auto_delete(): void
    {
        $capturedArgs = null;
        $channel      = $this->makeChannel();
        $channel->expects($this->once())
            ->method('queue_declare')
            ->willReturnCallback(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        (new RabbitMQ($this->makeConnection($channel)))->declare('my-queue');

        $this->assertFalse($capturedArgs[4]); // auto_delete
    }

    // publish

    public function test_publish_uses_default_exchange(): void
    {
        $capturedExchange = null;
        $channel          = $this->makeChannel();
        $channel->expects($this->once())
            ->method('basic_publish')
            ->willReturnCallback(function ($msg, $exchange) use (&$capturedExchange) {
                $capturedExchange = $exchange;
            });

        (new RabbitMQ($this->makeConnection($channel)))->publish('my-queue', $this->makeMessage());

        $this->assertSame('', $capturedExchange);
    }

    public function test_publish_routes_to_queue_as_routing_key(): void
    {
        $capturedKey = null;
        $channel     = $this->makeChannel();
        $channel->expects($this->once())
            ->method('basic_publish')
            ->willReturnCallback(function ($msg, $exchange, $routingKey) use (&$capturedKey) {
                $capturedKey = $routingKey;
            });

        (new RabbitMQ($this->makeConnection($channel)))->publish('my-queue', $this->makeMessage());

        $this->assertSame('my-queue', $capturedKey);
    }

    public function test_publish_encodes_message_body_as_json(): void
    {
        $capturedMsg = null;
        $channel     = $this->makeChannel();
        $channel->expects($this->once())
            ->method('basic_publish')
            ->willReturnCallback(function (AMQPMessage $msg) use (&$capturedMsg) {
                $capturedMsg = $msg;
            });

        (new RabbitMQ($this->makeConnection($channel)))->publish('my-queue', $this->makeMessage());

        $this->assertSame(
            json_encode(['event' => 'order.placed', 'id' => 'abc-123']),
            $capturedMsg->getBody()
        );
    }

    public function test_publish_sets_persistent_delivery_mode(): void
    {
        $capturedMsg = null;
        $channel     = $this->makeChannel();
        $channel->expects($this->once())
            ->method('basic_publish')
            ->willReturnCallback(function (AMQPMessage $msg) use (&$capturedMsg) {
                $capturedMsg = $msg;
            });

        (new RabbitMQ($this->makeConnection($channel)))->publish('my-queue', $this->makeMessage());

        $this->assertSame(AMQPMessage::DELIVERY_MODE_PERSISTENT, $capturedMsg->get('delivery_mode'));
    }

    public function test_publish_sets_json_content_type(): void
    {
        $capturedMsg = null;
        $channel     = $this->makeChannel();
        $channel->expects($this->once())
            ->method('basic_publish')
            ->willReturnCallback(function (AMQPMessage $msg) use (&$capturedMsg) {
                $capturedMsg = $msg;
            });

        (new RabbitMQ($this->makeConnection($channel)))->publish('my-queue', $this->makeMessage());

        $this->assertSame('application/json', $capturedMsg->get('content_type'));
    }

    // channel reuse

    public function test_reuses_single_channel_across_calls(): void
    {
        $channel    = $this->makeChannel();
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $queue = new RabbitMQ($connection);
        $queue->declare('my-queue');
        $queue->publish('my-queue', $this->makeMessage());
    }
}
