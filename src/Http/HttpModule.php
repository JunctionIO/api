<?php

namespace Junction\Api\Http;

use Meritum\Validation\Validator;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Bus\DispatcherInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Meritum\StructuredLogging\CorrelationId;
use Meritum\Serialization\FormatterInterface;
use Junction\Api\Event\EventRepositoryInterface;

final class HttpModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            Middleware\Correlation::class,
            fn(ContainerInterface $c) => new Middleware\Correlation($c->get(CorrelationId::class))
        );

        $kernel->define(
            Middleware\ValidateUuidId::class,
            fn(ContainerInterface $c) => new Middleware\ValidateUuidId($c->get(\Meritum\Validation\Rule\Uuid::class))
        );

        $kernel->define(
            Handler\JsonResponseHandler::class,
            fn(ContainerInterface $c) => new Handler\JsonResponseHandler($c->get(FormatterInterface::class))
        );

        // Event Middelware
        $kernel->define(
            Middleware\Event\All::class,
            fn(ContainerInterface $c) => new Middleware\Event\All($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Event\Exists::class,
            fn(ContainerInterface $c) => new Middleware\Event\Exists($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Middleware\Event\Validator::class,
            fn(ContainerInterface $c) => new Middleware\Event\Validator($c->get(Validator::class))
        );

        $kernel->define(
            Middleware\Event\Update::class,
            fn(ContainerInterface $c) => new Middleware\Event\Update($c->get(DispatcherInterface::class))
        );
    }
}
