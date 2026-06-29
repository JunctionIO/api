<?php

namespace Junction\Api\Http;

use Meritum\Validation\Validator;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\ApiToken\DecoderInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Meritum\StructuredLogging\CorrelationId;
use Meritum\Serialization\FormatterInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class HttpModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            Middleware\Correlation::class,
            fn(ContainerInterface $c) => new Middleware\Correlation($c->get(CorrelationId::class))
        );

        $kernel->define(
            Handler\JsonResponseHandler::class,
            fn(ContainerInterface $c) => new Handler\JsonResponseHandler($c->get(FormatterInterface::class))
        );

        // API Token
        $kernel->define(
            'api.token.relay',
            fn(ContainerInterface $c) => new Middleware\ValidateApiToken($c->get(DecoderInterface::class), 'relay')
        );

        $kernel->define(
            'api.token.system',
            fn(ContainerInterface $c) => new Middleware\ValidateApiToken($c->get(DecoderInterface::class), 'system')
        );

        $kernel->define(
            'api.token.management',
            fn(ContainerInterface $c) => new Middleware\ValidateApiToken($c->get(DecoderInterface::class), 'management')
        );

        // Relay Middleware
        $kernel->define(
            Middleware\Relay\ResolveTraceId::class,
            fn(ContainerInterface $c) => new Middleware\Relay\ResolveTraceId(
                $c->get(\Junction\Api\Trace\TraceId::class),
                $c->get(\Meritum\Validation\Rule\Uuid::class)
            )
        );

        $kernel->define(
            Middleware\Relay\Validate::class,
            fn(ContainerInterface $c) => new Middleware\Relay\Validate($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Relay\CreateEventLog::class,
            fn(ContainerInterface $c) => new Middleware\Relay\CreateEventLog($c->get(DispatcherInterface::class))
        );

        $kernel->define(Middleware\Relay\ValidateEvent::class, fn() => new Middleware\Relay\ValidateEvent());

        $kernel->define(
            Middleware\Relay\FindEvent::class,
            fn(ContainerInterface $c) => new Middleware\Relay\FindEvent($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Relay\Relay::class,
            fn(ContainerInterface $c) => new Middleware\Relay\Relay($c->get(DispatcherInterface::class))
        );

        // Event Middelware
        $kernel->define(
            Middleware\Event\All::class,
            fn(ContainerInterface $c) => new Middleware\Event\All($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Event\Find::class,
            fn(ContainerInterface $c) => new Middleware\Event\Find($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Event\Validator::class,
            fn(ContainerInterface $c) => new Middleware\Event\Validator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Event\Update::class,
            fn(ContainerInterface $c) => new Middleware\Event\Update($c->get(DispatcherInterface::class))
        );

        // EventLog Middleware
        $kernel->define(
            Middleware\EventLog\Find::class,
            fn(ContainerInterface $c) => new Middleware\EventLog\Find($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\EventLog\All::class,
            fn(ContainerInterface $c) => new Middleware\EventLog\All($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\EventLog\ParseEventFilter::class,
            fn(ContainerInterface $c) => new Middleware\EventLog\ParseEventFilter($c->get(EventRepositoryInterface::class))
        );

        // DestinationType Middleware
        $kernel->define(
            Middleware\DestinationType\All::class,
            fn(ContainerInterface $c) => new Middleware\DestinationType\All($c->get(DestinationTypeRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\DestinationType\Find::class,
            fn(ContainerInterface $c) => new Middleware\DestinationType\Find($c->get(DestinationTypeRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\DestinationType\UpsertValidator::class,
            fn(ContainerInterface $c) => new Middleware\DestinationType\UpsertValidator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\DestinationType\Upsert::class,
            fn(ContainerInterface $c) => new Middleware\DestinationType\Upsert($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\DestinationType\DeclareQueue::class,
            fn(ContainerInterface $c) => new Middleware\DestinationType\DeclareQueue($c->get(QueueInterface::class))
        );

        // Destination Middleware
        $kernel->define(
            Middleware\Destination\CreateValidator::class,
            fn(ContainerInterface $c) => new Middleware\Destination\CreateValidator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Destination\UpdateValidator::class,
            fn(ContainerInterface $c) => new Middleware\Destination\UpdateValidator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Destination\UpdateEventsValidator::class,
            fn(ContainerInterface $c) => new Middleware\Destination\UpdateEventsValidator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Destination\All::class,
            fn(ContainerInterface $c) => new Middleware\Destination\All($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Destination\Create::class,
            fn(ContainerInterface $c) => new Middleware\Destination\Create($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Destination\Update::class,
            fn(ContainerInterface $c) => new Middleware\Destination\Update($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Destination\Find::class,
            fn(ContainerInterface $c) => new Middleware\Destination\Find($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Destination\Delete::class,
            fn(ContainerInterface $c) => new Middleware\Destination\Delete($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\Destination\FindForUpdate::class,
            fn(ContainerInterface $c) => new Middleware\Destination\FindForUpdate($c->get(DestinationRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Destination\FindDestinationType::class,
            fn(ContainerInterface $c) => new Middleware\Destination\FindDestinationType($c->get(DestinationTypeRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Destination\UpdateEvents::class,
            fn(ContainerInterface $c) => new Middleware\Destination\UpdateEvents($c->get(DispatcherInterface::class))
        );

        // DestinationLog Middleware
        $kernel->define(
            Middleware\DestinationLog\AllForEventLog::class,
            fn(ContainerInterface $c) => new Middleware\DestinationLog\AllForEventLog($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\DestinationLog\AllForDestination::class,
            fn(ContainerInterface $c) => new Middleware\DestinationLog\AllForDestination($c->get(DispatcherInterface::class))
        );

        $kernel->define(
            Middleware\DestinationLog\UpdateValidator::class,
            fn(ContainerInterface $c) => new Middleware\DestinationLog\UpdateValidator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\DestinationLog\Update::class,
            fn(ContainerInterface $c) => new Middleware\DestinationLog\Update($c->get(DispatcherInterface::class))
        );
    }
}
