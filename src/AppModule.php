<?php

namespace Junction\Api;

use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Meritum\Http\HttpKernelInterface;
use Junction\Api\Event\EventSerializer;
use Junction\Api\Http\Middleware\BodyParser;
use Junction\Api\Http\Middleware\ValidateUuidId;
use Junction\Api\Http\Middleware\CreateResource;
use Junction\Api\Http\Handler\JsonResponseHandler;
use Junction\Api\Http\Middleware\ValidateContentType;
use Junction\Api\Http\Middleware\ParsePaginationQuery;
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
        $kernel->addMiddleware(new ValidateContentType());
        $kernel->addMiddleware(new BodyParser());

        // Routes
        $kernel->addRoute('GET', '/v1/events', JsonResponseHandler::class)
               ->addMiddleware(new ParsePaginationQuery())
               ->addMiddleware(Http\Middleware\Event\All::class)
               ->addMiddleware(new CreateResource(new EventSerializer()));

        $kernel->addRoute('PATCH', '/v1/events/{id}', JsonResponseHandler::class)
               ->addMiddleware(ValidateUuidId::class)
               ->addMiddleware(Http\Middleware\Event\Exists::class)
               ->addMiddleware(Http\Middleware\Event\Validator::class)
               ->addMiddleware(Http\Middleware\Event\Update::class)
               ->addMiddleware(new CreateResource(new EventSerializer()));

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
