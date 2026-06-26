<?php

namespace Junction\Api\Test\Queue;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\Queue\QueueModule;
use Junction\Api\Queue\RabbitMQ;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class QueueModuleTest extends TestCase
{
    /**
     * @return array{array<string, callable>, array<string, DefinitionInterface>}
     */
    private function captureDefinitions(): array
    {
        $factories   = [];
        $definitions = [];

        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('share')->willReturnSelf();
        $definition->method('tag')->willReturnSelf();

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factories, &$definitions) {
                $factories[$id]   = $f;
                $definitions[$id] = $definition;
                return $definition;
            });

        (new QueueModule())->register($kernel);

        return [$factories, $definitions];
    }

    // register

    public function test_registers_amqp_connection(): void
    {
        [$factories] = $this->captureDefinitions();

        $this->assertArrayHasKey(AMQPStreamConnection::class, $factories);
    }

    public function test_registers_queue_interface(): void
    {
        [$factories] = $this->captureDefinitions();

        $this->assertArrayHasKey(QueueInterface::class, $factories);
    }

    public function test_queue_interface_definition_is_shared(): void
    {
        $sharedIds  = [];
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('share')
            ->willReturnCallback(function () use ($definition, &$sharedIds) {
                $sharedIds[] = true;
                return $definition;
            });

        $lastId = null;
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id) use ($definition, &$lastId) {
                $lastId = $id;
                return $definition;
            });

        (new QueueModule())->register($kernel);

        $this->assertNotEmpty($sharedIds);
    }

    public function test_queue_interface_factory_produces_rabbit_mq(): void
    {
        [$factories] = $this->captureDefinitions();

        $connection = $this->createMock(AMQPStreamConnection::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(AMQPStreamConnection::class)->willReturn($connection);

        $this->assertInstanceOf(RabbitMQ::class, $factories[QueueInterface::class]($container));
    }

    // config

    public function test_config_has_four_keys(): void
    {
        $this->assertCount(4, (new QueueModule())->config(Environment::Testing));
    }

    public function test_config_contains_queue_host_key(): void
    {
        $this->assertArrayHasKey('queue.host', (new QueueModule())->config(Environment::Testing));
    }

    public function test_config_contains_queue_port_key(): void
    {
        $this->assertArrayHasKey('queue.port', (new QueueModule())->config(Environment::Testing));
    }

    public function test_config_contains_queue_user_key(): void
    {
        $this->assertArrayHasKey('queue.user', (new QueueModule())->config(Environment::Testing));
    }

    public function test_config_contains_queue_password_key(): void
    {
        $this->assertArrayHasKey('queue.password', (new QueueModule())->config(Environment::Testing));
    }
}
