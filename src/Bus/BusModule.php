<?php

namespace Junction\Api\Bus;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;

final class BusModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            TransactionMiddleware::class,
            fn(ContainerInterface $c) => new TransactionMiddleware($c->get(DatabaseManagerInterface::class))
        )->tag('bus.middleware');
    }
}
