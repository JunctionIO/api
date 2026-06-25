<?php

namespace Junction\Api\Test\Validation;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Validation\ConfigSchemaRule;
use Junction\Api\Validation\ValidationModule;
use PHPUnit\Framework\TestCase;

final class ValidationModuleTest extends TestCase
{
    public function test_registers_config_schema_rule(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->once())
            ->method('define')
            ->with(ConfigSchemaRule::class)
            ->willReturn($definition);

        (new ValidationModule())->register($kernel);
    }

    public function test_tags_rule_as_validation_rules(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->once())
            ->method('tag')
            ->with('validation.rules');

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')->willReturn($definition);

        (new ValidationModule())->register($kernel);
    }

    public function test_factory_produces_config_schema_rule(): void
    {
        $factory    = null;
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factory) {
                $factory = $f;
                return $definition;
            });

        (new ValidationModule())->register($kernel);

        $this->assertInstanceOf(ConfigSchemaRule::class, $factory());
    }
}
