<?php

namespace Junction\Api\Test\Event;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\Event\EventModule;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Event\Command\FindOrCreateHandler;
use Junction\Api\Event\Command\UpdateHandler;
use Junction\Api\Event\Repository\PostgresEventRepository;

final class EventModuleTest extends TestCase
{
    public function test_registers_all_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(3))
            ->method('define')
            ->willReturn($definition);

        (new EventModule())->register($kernel);
    }

    public function test_factory_produces_postgres_event_repository(): void
    {
        [$factories] = $this->captureFactories();

        $db = $this->createMock(DatabaseManagerInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DatabaseManagerInterface::class)->willReturn($db);

        $this->assertInstanceOf(PostgresEventRepository::class, $factories[EventRepositoryInterface::class]($container));
    }

    public function test_factory_produces_find_or_create_handler(): void
    {
        [$factories] = $this->captureFactories();

        $repo = $this->createMock(EventRepositoryInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(EventRepositoryInterface::class)->willReturn($repo);

        $this->assertInstanceOf(FindOrCreateHandler::class, $factories[FindOrCreateHandler::class]($container));
    }

    public function test_factory_produces_update_handler(): void
    {
        [$factories] = $this->captureFactories();

        $repo = $this->createMock(EventRepositoryInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(EventRepositoryInterface::class)->willReturn($repo);

        $this->assertInstanceOf(UpdateHandler::class, $factories[UpdateHandler::class]($container));
    }

    /**
     * @return array{array<string, callable>}
     */
    private function captureFactories(): array
    {
        $factories = [];
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factories) {
                $factories[$id] = $f;
                return $definition;
            });

        (new EventModule())->register($kernel);

        return [$factories];
    }
}
