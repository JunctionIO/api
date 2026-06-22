<?php

namespace Junction\Api\Exception;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ExceptionModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(
            Translator\ModelNotFoundHandler::class,
            fn(ContainerInterface $c) => new Translator\ModelNotFoundHandler($c->get(ServerRequestInterface::class))
        )->tag('exception.translator.handlers');
    }
}
