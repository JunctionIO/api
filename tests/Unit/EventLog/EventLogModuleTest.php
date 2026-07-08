<?php

namespace Junction\Api\Test\Unit\EventLog;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLogModule;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Junction\Api\EventLog\Command\CreateHandler;
use Junction\Api\EventLog\Command\QueryAllHandler;
use Junction\Api\EventLog\Command\QueryFindHandler;
use Junction\Api\EventLog\Repository\PostgresEventLogRepository;

final class EventLogModuleTest extends TestCase
{
    public function test_registers_all_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(4))
            ->method('define')
            ->willReturn($definition);

        (new EventLogModule())->register($kernel);
    }

    public function test_factory_produces_postgres_event_log_repository(): void
    {
        [$factories] = $this->captureFactories();

        $db        = $this->createMock(DatabaseManagerInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DatabaseManagerInterface::class)->willReturn($db);

        $this->assertInstanceOf(
            PostgresEventLogRepository::class,
            $factories[EventLogRepositoryInterface::class]($container)
        );
    }

    public function test_factory_produces_query_find_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [EventLogRepositoryInterface::class, $this->createMock(EventLogRepositoryInterface::class)],
            [EventRepositoryInterface::class, $this->createMock(EventRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(
            QueryFindHandler::class,
            $factories[QueryFindHandler::class]($container)
        );
    }

    public function test_factory_produces_query_all_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [EventLogRepositoryInterface::class, $this->createMock(EventLogRepositoryInterface::class)],
            [EventRepositoryInterface::class, $this->createMock(EventRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(
            QueryAllHandler::class,
            $factories[QueryAllHandler::class]($container)
        );
    }

    public function test_factory_produces_create_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [EventLogRepositoryInterface::class, $this->createMock(EventLogRepositoryInterface::class)],
        ]);

        $this->assertInstanceOf(
            CreateHandler::class,
            $factories[CreateHandler::class]($container)
        );
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

        (new EventLogModule())->register($kernel);

        return [$factories];
    }
}
