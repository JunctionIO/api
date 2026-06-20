<?php

namespace Junction\Api\DestinationType;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;

final class DestinationTypeModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            DestinationTypeRepositoryInterface::class,
            fn(ContainerInterface $c) => new Repository\PostgresDestinationTypeRepository($c->get(DatabaseManagerInterface::class))
        );
    }
}
