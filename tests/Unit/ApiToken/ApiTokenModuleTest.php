<?php

namespace Junction\Api\Test\Unit\ApiToken;

use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Junction\Api\ApiToken\ApiTokenModule;
use Junction\Api\ApiToken\DecoderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ApiTokenModuleTest extends TestCase
{
    public function test_registers_decoder(): void
    {
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects($this->once())
            ->method('define')
            ->with(DecoderInterface::class, $this->isType('callable'))
            ->willReturn($definition);

        new ApiTokenModule()->register($kernel);
    }

    public function test_factory_produces_decoder(): void
    {
        $factory    = null;
        $definition = $this->createMock(DefinitionInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('define')
            ->willReturnCallback(function (string $id, callable $f) use ($definition, &$factory) {
                $factory = $f;
                return $definition;
            });

        new ApiTokenModule()->register($kernel);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('kernel.config')->willReturn(['jwt.secret' => 'test-secret-key-at-least-32-chars']);

        $this->assertInstanceOf(DecoderInterface::class, $factory($container));
    }

    public function test_config_provides_jwt_secret_key(): void
    {
        $config = new ApiTokenModule()->config(Environment::Testing);

        $this->assertArrayHasKey('jwt.secret', $config);
    }
}
