<?php

namespace Junction\Api\Trace;

use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Module\ModuleInterface;

final class TraceModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(TraceId::class, fn() => new TraceId())->share();
    }
}
