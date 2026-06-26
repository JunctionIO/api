<?php

namespace Junction\Api\Queue;

use Georgeff\Kernel\Support\Env;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Georgeff\Kernel\Module\ConfigurableModuleInterface;

final class QueueModule implements ConfigurableModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(AMQPStreamConnection::class, fn(ContainerInterface $c) => $this->createAmqpConnection($c));

        $kernel->define(
            QueueInterface::class,
            fn(ContainerInterface $c) => new RabbitMQ($c->get(AMQPStreamConnection::class))
        )->share();
    }

    public function config(Environment $env): array
    {
        return [
            'queue.host'     => Env::get('QUEUE_HOST'),
            'queue.port'     => Env::get('QUEUE_PORT'),
            'queue.user'     => Env::get('QUEUE_USER'),
            'queue.password' => Env::get('QUEUE_PASSWORD'),
        ];
    }

    private function createAmqpConnection(ContainerInterface $c): AMQPStreamConnection
    {
        /**
         * @var array{
         *      'queue.host': string,
         *      'queue.port': int,
         *      'queue.user': string,
         *      'queue.password': string
         * }
         */
        $config = $c->get('kernel.config');

        return new AMQPStreamConnection(
            host: $config['queue.host'],
            port: $config['queue.port'],
            user: $config['queue.user'],
            password: $config['queue.password']
        );
    }
}
