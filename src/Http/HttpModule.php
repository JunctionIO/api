<?php

namespace Junction\Api\Http;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Meritum\StructuredLogging\CorrelationId;

final class HttpModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            Middleware\Correlation::class,
            fn(ContainerInterface $c) => new Middleware\Correlation($c->get(CorrelationId::class))
        );
    }
}
