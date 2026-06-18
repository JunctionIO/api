<?php

namespace Junction\Api\Test\KernelHook;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\DI\DefinitionInterface;
use Georgeff\Kernel\Debug\DebuggableInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Kernel\Module\ModuleRepositoryInterface;
use Junction\Api\KernelHook\LogDebugInfo;

final class LogDebugInfoTest extends TestCase
{
    public function test_does_not_log_when_debug_is_disabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('debug');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        $kernel = $this->makeKernel(false, $container, []);

        (new LogDebugInfo())($kernel);
    }

    public function test_logs_debug_info_when_debug_is_enabled(): void
    {
        $debugInfo = ['foo' => 'bar'];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('kernel.debug', ['kernel_debugInfo' => $debugInfo]);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(LoggerInterface::class)->willReturn($logger);

        $kernel = $this->makeKernel(true, $container, $debugInfo);

        (new LogDebugInfo())($kernel);
    }

    private function makeKernel(bool $isDebug, ContainerInterface $container, array $debugInfo): KernelInterface
    {
        return new class($isDebug, $container, $debugInfo) implements KernelInterface, DebuggableInterface {
            public function __construct(
                private bool $debug,
                private ContainerInterface $container,
                private array $info,
            ) {}

            public function isDebug(): bool { return $this->debug; }
            public function getContainer(): ContainerInterface { return $this->container; }
            public function getDebugInfo(): array { return $this->info; }

            public function boot(): void {}
            public function shutdown(): void {}
            public function isBooting(): bool { return false; }
            public function isBooted(): bool { return false; }
            public function isShutdown(): bool { return false; }
            public function getEnvironment(): string { return 'test'; }
            public function onBooting(callable $callback): static { return $this; }
            public function onBooted(callable $callback): static { return $this; }
            public function onShutdown(callable $callback): static { return $this; }
            public function afterShutdown(callable $callback): static { return $this; }
            public function addDefinition(string $id, callable $factory, bool $shared = false, array $aliases = [], array $tags = []): static { return $this; }
            public function define(string $id, callable $factory): DefinitionInterface { throw new \LogicException('not implemented'); }
            public function tag(string $id, array $tags): static { return $this; }
            public function decorate(string $id, callable $decorator): static { return $this; }
            public function addModule(ModuleInterface $module): static { return $this; }
            public function addRepository(ModuleRepositoryInterface $repository): static { return $this; }
            public function getStartTime(): float { return 0.0; }
        };
    }
}
