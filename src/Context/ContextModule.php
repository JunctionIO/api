<?php

namespace Junction\Api\Context;

use Junction\Api\Trace\TraceId;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ContextModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(EnvironmentEnricher::class, fn() => new EnvironmentEnricher())->tag('log.context.enrichers');

        $kernel->define(
            TraceIdEnricher::class,
            fn(ContainerInterface $c) => new TraceIdEnricher(
                $c->get(TraceId::class),
                $c->get(ServerRequestInterface::class)
            )
        )->tag('log.context.enrichers');
    }
}
