<?php

namespace Junction\Api\Test\Unit\Destination;

use Georgeff\Bus\DispatcherInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Destination\Command\CreateHandler;
use Junction\Api\Destination\Command\DeleteHandler;
use Junction\Api\Destination\Command\QueryActiveByEventHandler;
use Junction\Api\Destination\Command\QueryAllHandler;
use Junction\Api\Destination\Command\QueryFindHandler;
use Junction\Api\Destination\Command\UpdateHandler;
use Junction\Api\Destination\Command\UpdateRelatedEventsHandler;
use Junction\Api\Destination\DestinationModule;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\Destination\Repository\PostgrestDestinationRepository;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Event\EventRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class DestinationModuleTest extends TestCase
{
    public function test_registers_all_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(8))
            ->method('define')
            ->willReturn($definition);

        (new DestinationModule())->register($kernel);
    }

    public function test_factory_produces_postgres_repository(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DatabaseManagerInterface::class)
            ->willReturn($this->createMock(DatabaseManagerInterface::class));

        $this->assertInstanceOf(
            PostgrestDestinationRepository::class,
            $factories[DestinationRepositoryInterface::class]($container)
        );
    }

    public function test_factory_produces_create_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
            [DispatcherInterface::class, $this->createMock(DispatcherInterface::class)],
        ]);

        $this->assertInstanceOf(CreateHandler::class, $factories[CreateHandler::class]($container));
    }

    public function test_factory_produces_update_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
            [DestinationTypeRepositoryInterface::class, $this->createMock(DestinationTypeRepositoryInterface::class)],
            [EventRepositoryInterface::class, $this->createMock(EventRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(UpdateHandler::class, $factories[UpdateHandler::class]($container));
    }

    public function test_factory_produces_query_find_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
            [DestinationTypeRepositoryInterface::class, $this->createMock(DestinationTypeRepositoryInterface::class)],
            [EventRepositoryInterface::class, $this->createMock(EventRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(QueryFindHandler::class, $factories[QueryFindHandler::class]($container));
    }

    public function test_factory_produces_query_all_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
            [DestinationTypeRepositoryInterface::class, $this->createMock(DestinationTypeRepositoryInterface::class)],
            [EventRepositoryInterface::class, $this->createMock(EventRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(QueryAllHandler::class, $factories[QueryAllHandler::class]($container));
    }

    public function test_factory_produces_delete_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(DeleteHandler::class, $factories[DeleteHandler::class]($container));
    }

    public function test_factory_produces_update_related_events_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DispatcherInterface::class, $this->createMock(DispatcherInterface::class)],
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(UpdateRelatedEventsHandler::class, $factories[UpdateRelatedEventsHandler::class]($container));
    }

    public function test_factory_produces_query_active_by_event_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [DestinationRepositoryInterface::class, $this->createMock(DestinationRepositoryInterface::class)],
            [DestinationTypeRepositoryInterface::class, $this->createMock(DestinationTypeRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(QueryActiveByEventHandler::class, $factories[QueryActiveByEventHandler::class]($container));
    }

    /**
     * @return array{array<string, callable>}
     */
    private function captureFactories(): array
    {
        $factories  = [];
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factories) {
                $factories[$id] = $f;
                return $definition;
            });

        (new DestinationModule())->register($kernel);

        return [$factories];
    }
}
