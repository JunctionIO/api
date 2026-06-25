<?php

namespace Junction\Api\Event;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;

final class EventModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            EventRepositoryInterface::class,
            fn(ContainerInterface $c) => new Repository\PostgresEventRepository($c->get(DatabaseManagerInterface::class))
        );

        $kernel->define(
            Command\FindOrCreateHandler::class,
            fn(ContainerInterface $c) => new Command\FindOrCreateHandler($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Command\FindManyOrCreateHandler::class,
            fn(ContainerInterface $c) => new Command\FindManyOrCreateHandler($c->get(EventRepositoryInterface::class))
        );

        $kernel->define(
            Command\UpdateHandler::class,
            fn(ContainerInterface $c) => new Command\UpdateHandler($c->get(EventRepositoryInterface::class))
        );
    }
}
