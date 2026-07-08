<?php

namespace Junction\Api\Test\Unit\Trace;

use PHPUnit\Framework\TestCase;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Junction\Api\Trace\TraceId;
use Junction\Api\Trace\TraceModule;

final class TraceModuleTest extends TestCase
{
    public function test_registers_trace_id_as_shared(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->once())->method('share');

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->once())
            ->method('define')
            ->with(TraceId::class, $this->isType('callable'))
            ->willReturn($definition);

        (new TraceModule())->register($kernel);
    }

    public function test_factory_produces_trace_id_instance(): void
    {
        $factory = null;

        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('share')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factory) {
                $factory = $f;
                return $definition;
            });

        (new TraceModule())->register($kernel);

        $this->assertInstanceOf(TraceId::class, $factory());
    }
}
