<?php

namespace Junction\Api;

use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Meritum\Http\HttpKernelInterface;
use Georgeff\Kernel\Module\ConfigurableModuleInterface;

final class AppModule implements ConfigurableModuleInterface
{
    /**
     * Register kernel services
     */
    public function register(KernelInterface $kernel): void
    {
        assert($kernel instanceof HttpKernelInterface);

        // Global Middleware
        $kernel->addMiddleware(Http\Middleware\Correlation::class);
        $kernel->addMiddleware(new Http\Middleware\SetRouteArgumentsOnRequest());

        // Routes

        // Kernel Hooks
        $kernel->afterShutdown(new KernelHook\LogDebugInfo());
    }

    /**
     * Application config
     *
     * Values registered here are stored in the container under the key `kernel.config`
     *
     * @return array<string, mixed>
     */
    public function config(Environment $env): array
    {
        return [];
    }
}
