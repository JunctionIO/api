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
    }
}
