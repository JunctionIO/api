<?php

namespace Junction\Api\Context;

use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Module\ModuleInterface;

final class ContextModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(EnvironmentEnricher::class, fn() => new EnvironmentEnricher())->tag('log.context.enrichers');
    }
}
