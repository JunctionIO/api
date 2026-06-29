<?php

namespace Junction\Api\ApiToken;

use Georgeff\Kernel\Support\Env;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ConfigurableModuleInterface;

final class ApiTokenModule implements ConfigurableModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(DecoderInterface::class, fn(ContainerInterface $c) => $this->getTokenDecoder($c));
    }

    public function config(Environment $env): array
    {
        return [
            'jwt.secret' => Env::get('JWT_SECRET', ''),
        ];
    }

    private function getTokenDecoder(ContainerInterface $container): Decoder
    {
        /** @var array{'jwt.secret': string} */
        $config = $container->get('kernel.config');

        return new Decoder($config['jwt.secret']);
    }
}
