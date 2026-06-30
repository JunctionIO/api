<?php

namespace Junction\Api\Test\Exception;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\Exception\ExceptionModule;
use Junction\Api\Exception\Translator\JunctionDomainHandler;
use Junction\Api\Exception\Translator\ModelNotFoundHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ExceptionModuleTest extends TestCase
{
    public function test_registers_all_definitions(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->exactly(2))
            ->method('define')
            ->willReturn($definition);

        (new ExceptionModule())->register($kernel);
    }

    public function test_tags_all_handlers_as_exception_translator_handlers(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->exactly(2))
            ->method('tag')
            ->with('exception.translator.handlers')
            ->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')->willReturn($definition);

        (new ExceptionModule())->register($kernel);
    }

    public function test_factory_produces_model_not_found_handler(): void
    {
        [$factories] = $this->captureFactories();

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with(ServerRequestInterface::class)
            ->willReturn($this->createMock(ServerRequestInterface::class));

        $this->assertInstanceOf(ModelNotFoundHandler::class, $factories[ModelNotFoundHandler::class]($container));
    }

    public function test_factory_produces_junction_domain_handler(): void
    {
        [$factories] = $this->captureFactories();

        $this->assertInstanceOf(JunctionDomainHandler::class, $factories[JunctionDomainHandler::class]());
    }

    /**
     * @return array{array<string, callable>}
     */
    private function captureFactories(): array
    {
        $factories  = [];
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->method('tag')->willReturn($definition);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factories) {
                $factories[$id] = $f;
                return $definition;
            });

        (new ExceptionModule())->register($kernel);

        return [$factories];
    }
}
