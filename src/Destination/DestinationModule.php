<?php

namespace Junction\Api\Destination;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Bus\DispatcherInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Junction\Api\Event\EventRepositoryInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;

final class DestinationModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            DestinationRepositoryInterface::class,
            fn(ContainerInterface $c) => new Repository\PostgrestDestinationRepository($c->get(DatabaseManagerInterface::class))
        );

        $kernel->define(
            Command\CreateHandler::class,
            fn(ContainerInterface $c) => new Command\CreateHandler(
                $c->get(DestinationRepositoryInterface::class),
                $c->get(DispatcherInterface::class)
            )
        );

        $kernel->define(
            Command\UpdateHandler::class,
            fn(ContainerInterface $c) => new Command\UpdateHandler(
                $c->get(DestinationRepositoryInterface::class),
                $c->get(DestinationTypeRepositoryInterface::class),
                $c->get(EventRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\QueryFindHandler::class,
            fn(ContainerInterface $c) => new Command\QueryFindHandler(
                $c->get(DestinationRepositoryInterface::class),
                $c->get(DestinationTypeRepositoryInterface::class),
                $c->get(EventRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\QueryAllHandler::class,
            fn(ContainerInterface $c) => new Command\QueryAllHandler(
                $c->get(DestinationRepositoryInterface::class),
                $c->get(DestinationTypeRepositoryInterface::class),
                $c->get(EventRepositoryInterface::class)
            )
        );

        $kernel->define(
            Command\DeleteHandler::class,
            fn(ContainerInterface $c) => new Command\DeleteHandler($c->get(DestinationRepositoryInterface::class))
        );

        $kernel->define(
            Command\UpdateRelatedEventsHandler::class,
            fn(ContainerInterface $c) => new Command\UpdateRelatedEventsHandler(
                $c->get(DispatcherInterface::class),
                $c->get(DestinationRepositoryInterface::class)
            )
        );
    }
}
