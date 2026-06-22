<?php

namespace Junction\Api\EventLog;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;

final class EventLogModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            EventLogRepositoryInterface::class,
            fn(ContainerInterface $c) => new Repository\PostgresEventLogRepository($c->get(DatabaseManagerInterface::class))
        );

        $kernel->define(
            Command\QueryFindHandler::class,
            fn(ContainerInterface $c) => new Command\QueryFindHandler(
                $c->get(EventLogRepositoryInterface::class),
                $c->get(EventRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\QueryAllHandler::class,
            fn(ContainerInterface $c) => new Command\QueryAllHandler(
                $c->get(EventLogRepositoryInterface::class),
                $c->get(EventRepositoryInterface::class)
            )
        );
    }
}
