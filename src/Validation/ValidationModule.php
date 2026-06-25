<?php

namespace Junction\Api\Validation;

use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Module\ModuleInterface;

final class ValidationModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(ConfigSchemaRule::class, fn() => new ConfigSchemaRule())->tag('validation.rules');
    }
}
