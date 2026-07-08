<?php

namespace Junction\Api\Test\Unit\Relay;

use Georgeff\Bus\DispatcherInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\Relay\Command\QueryEventHandler;
use Junction\Api\Relay\Command\RelayHandler;
use Junction\Api\Relay\RelayModule;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class RelayModuleTest extends TestCase
{
    public function test_registers_two_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(2))
            ->method('define')
            ->willReturn($definition);

        (new RelayModule())->register($kernel);
    }

    public function test_factory_produces_query_event_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DispatcherInterface::class)
            ->willReturn($this->createMock(DispatcherInterface::class));

        $this->assertInstanceOf(
            QueryEventHandler::class,
            $factories[QueryEventHandler::class]($container)
        );
    }

    public function test_factory_produces_relay_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [QueueInterface::class, $this->createMock(QueueInterface::class)],
            [DispatcherInterface::class, $this->createMock(DispatcherInterface::class)],
        ]);

        $this->assertInstanceOf(
            RelayHandler::class,
            $factories[RelayHandler::class]($container)
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

        (new RelayModule())->register($kernel);

        return [$factories];
    }
}
