<?php

namespace Junction\Api\DestinationLog;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\Destination\DestinationRepositoryInterface;

final class DestinationLogModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            DestinationLogRepositoryInterface::class,
            fn(ContainerInterface $c) => new Repository\PostgresDestinatonLogRepository($c->get(DatabaseManagerInterface::class))
        );

        $kernel->define(
            Command\QueryAllForEventLogHandler::class,
            fn(ContainerInterface $c) => new Command\QueryAllForEventLogHandler(
                $c->get(DestinationLogRepositoryInterface::class),
                $c->get(EventLogRepositoryInterface::class),
                $c->get(DestinationRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\QueryAllForDestinationHandler::class,
            fn(ContainerInterface $c) => new Command\QueryAllForDestinationHandler(
                $c->get(DestinationLogRepositoryInterface::class),
                $c->get(EventLogRepositoryInterface::class),
                $c->get(DestinationRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\CreateManyHandler::class,
            fn(ContainerInterface $c) => new Command\CreateManyHandler($c->get(DestinationLogRepositoryInterface::class))
        );

        $kernel->define(
            Command\UpdateHandler::class,
            fn(ContainerInterface $c) => new Command\UpdateHandler($c->get(DestinationLogRepositoryInterface::class))
        );
    }
}
