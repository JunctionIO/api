<?php

namespace Junction\Api\Test\Event;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\Event\EventModule;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Event\Repository\PostgresEventRepository;

final class EventModuleTest extends TestCase
{
    public function test_registers_event_repository_interface(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->once())
            ->method('define')
            ->with(EventRepositoryInterface::class, $this->isType('callable'))
            ->willReturn($definition);

        (new EventModule())->register($kernel);
    }

    public function test_factory_produces_postgres_event_repository(): void
    {
        $factory = null;

        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factory) {
                $factory = $f;
                return $definition;
            });

        (new EventModule())->register($kernel);

        $db = $this->createMock(DatabaseManagerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DatabaseManagerInterface::class)->willReturn($db);

        $this->assertInstanceOf(PostgresEventRepository::class, $factory($container));
    }
}
