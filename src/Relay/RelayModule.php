<?php

namespace Junction\Api\Relay;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Bus\DispatcherInterface;
use Georgeff\Kernel\Module\ModuleInterface;

final class RelayModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            Command\QueryEventHandler::class,
            fn(ContainerInterface $c) => new Command\QueryEventHandler($c->get(DispatcherInterface::class))
        );
    }
}
