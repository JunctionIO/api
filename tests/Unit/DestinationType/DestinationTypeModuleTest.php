<?php

namespace Junction\Api\Test\Unit\DestinationType;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\DestinationType\DestinationTypeModule;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\DestinationType\Repository\PostgresDestinationTypeRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class DestinationTypeModuleTest extends TestCase
{
    public function test_registers_repository_definition(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(2))
            ->method('define')
            ->willReturn($definition);

        (new DestinationTypeModule())->register($kernel);
    }

    public function test_factory_produces_postgres_repository(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(DatabaseManagerInterface::class)
            ->willReturn($this->createMock(DatabaseManagerInterface::class));

        $this->assertInstanceOf(PostgresDestinationTypeRepository::class, $factories[DestinationTypeRepositoryInterface::class]($container));
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

        (new DestinationTypeModule())->register($kernel);

        return [$factories];
    }
}
