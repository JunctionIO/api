<?php

namespace Junction\Api\Test\Context;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Context\ContextModule;
use Junction\Api\Context\EnvironmentEnricher;
use PHPUnit\Framework\TestCase;

final class ContextModuleTest extends TestCase
{
    public function test_registers_environment_enricher(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->once())
            ->method('define')
            ->with(EnvironmentEnricher::class, $this->isType('callable'))
            ->willReturn($definition);

        (new ContextModule())->register($kernel);
    }

    public function test_tags_environment_enricher_as_log_context_enricher(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->once())
            ->method('tag')
            ->with('log.context.enrichers')
            ->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')->willReturn($definition);

        (new ContextModule())->register($kernel);
    }

    public function test_factory_produces_environment_enricher(): void
    {
        $factory    = null;
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factory) {
                $factory = $f;
                return $definition;
            });

        (new ContextModule())->register($kernel);

        $this->assertInstanceOf(EnvironmentEnricher::class, $factory());
    }
}
