<?php

namespace Junction\Api;

use Georgeff\Kernel\KernelInterface;
use Meritum\Http\HttpKernelInterface;
use Junction\Api\Event\EventSerializer;
use Georgeff\Kernel\Module\ModuleInterface;
use Junction\Api\Http\Middleware\BodyParser;
use Junction\Api\EventLog\EventLogSerializer;
use Junction\Api\Http\Middleware\SetStatusCode;
use Junction\Api\Http\Middleware\CreateResource;
use Junction\Api\Http\Handler\JsonResponseHandler;
use Junction\Api\Http\Handler\EmptyResponseHandler;
use Junction\Api\Destination\DestinationSerializer;
use Junction\Api\Http\Middleware\ValidateContentType;
use Junction\Api\Http\Middleware\ParsePaginationQuery;
use Junction\Api\DestinationType\DestinationTypeSerializer;

final class AppModule implements ModuleInterface
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
        $kernel->addRoute('GET', '/v0/events', JsonResponseHandler::class)
               ->addMiddleware(new ParsePaginationQuery())
               ->addMiddleware(Http\Middleware\Event\All::class)
               ->addMiddleware(new CreateResource(new EventSerializer()));

        $kernel->addRoute('GET', '/v0/events/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Event\Find::class)
               ->addMiddleware(new CreateResource(new EventSerializer()));

        $kernel->addRoute('PATCH', '/v0/events/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Event\Validator::class)
               ->addMiddleware(Http\Middleware\Event\Update::class)
               ->addMiddleware(new CreateResource(new EventSerializer()));

        $kernel->addRoute('GET', '/v0/event-logs', JsonResponseHandler::class)
               ->addMiddleware(new ParsePaginationQuery())
               ->addMiddleware(Http\Middleware\EventLog\ParseEventFilter::class)
               ->addMiddleware(Http\Middleware\EventLog\All::class)
               ->addMiddleware(new CreateResource(new EventLogSerializer()));

        $kernel->addRoute('GET', '/v0/event-logs/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\EventLog\Find::class)
               ->addMiddleware(new CreateResource(new EventLogSerializer()));

        $kernel->addRoute('GET', '/v0/destination-types', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\DestinationType\All::class)
               ->addMiddleware(new CreateResource(new DestinationTypeSerializer()));

        $kernel->addRoute('GET', '/v0/destination-types/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\DestinationType\Find::class)
               ->addMiddleware(new CreateResource(new DestinationTypeSerializer()));

        $kernel->addRoute('GET', '/v0/destinations', JsonResponseHandler::class)
               ->addMiddleware(new ParsePaginationQuery())
               ->addMiddleware(Http\Middleware\Destination\All::class)
               ->addMiddleware(new CreateResource(new DestinationSerializer()));

        $kernel->addRoute('POST', '/v0/destinations', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Destination\FindDestinationType::class)
               ->addMiddleware(Http\Middleware\Destination\CreateValidator::class)
               ->addMiddleware(Http\Middleware\Destination\Create::class)
               ->addMiddleware(new CreateResource(new DestinationSerializer()))
               ->addMiddleware(new SetStatusCode(201));

        $kernel->addRoute('GET', '/v0/destinations/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Destination\Find::class)
               ->addMiddleware(new CreateResource(new DestinationSerializer()));

        $kernel->addRoute('PATCH', '/v0/destinations/{id}', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Destination\FindForUpdate::class)
               ->addMiddleware(Http\Middleware\Destination\FindDestinationType::class)
               ->addMiddleware(Http\Middleware\Destination\UpdateValidator::class)
               ->addMiddleware(Http\Middleware\Destination\Update::class)
               ->addMiddleware(new CreateResource(new DestinationSerializer()));

        $kernel->addRoute('DELETE', '/v0/destinations/{id}', new EmptyResponseHandler())
               ->addMiddleware(Http\Middleware\Destination\Delete::class);

        $kernel->addRoute('PUT', '/v0/destinations/{id}/events', JsonResponseHandler::class)
               ->addMiddleware(Http\Middleware\Destination\UpdateEventsValidator::class)
               ->addMiddleware(Http\Middleware\Destination\UpdateEvents::class)
               ->addMiddleware(new CreateResource(new DestinationSerializer()));

        // Kernel Hooks
        $kernel->afterShutdown(new KernelHook\LogDebugInfo());
    }
}
