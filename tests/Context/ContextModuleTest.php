<?php

namespace Junction\Api\Test\Context;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Context\ContextModule;
use Junction\Api\Context\EnvironmentEnricher;
use Junction\Api\Context\TraceIdEnricher;
use Junction\Api\Trace\TraceId;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ContextModuleTest extends TestCase
{
    public function test_registers_all_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(2))
            ->method('define')
            ->willReturn($definition);

        (new ContextModule())->register($kernel);
    }

    public function test_tags_all_enrichers_as_log_context_enrichers(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->exactly(2))
            ->method('tag')
            ->with('log.context.enrichers')
            ->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')->willReturn($definition);

        (new ContextModule())->register($kernel);
    }

    public function test_factory_produces_environment_enricher(): void
    {
        [$factories] = $this->captureFactories();

        $this->assertInstanceOf(EnvironmentEnricher::class, $factories[EnvironmentEnricher::class]());
    }

    public function test_factory_produces_trace_id_enricher(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [TraceId::class, new TraceId()],
            [ServerRequestInterface::class, $this->createMock(ServerRequestInterface::class)],
        ]);

        $this->assertInstanceOf(TraceIdEnricher::class, $factories[TraceIdEnricher::class]($container));
    }

    /**
     * @return array{array<string, callable>}
     */
    private function captureFactories(): array
    {
        $factories = [];
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factories) {
                $factories[$id] = $f;
                return $definition;
            });

        (new ContextModule())->register($kernel);

        return [$factories];
    }
}
