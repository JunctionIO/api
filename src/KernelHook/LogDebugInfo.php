<?php

namespace Junction\Api\KernelHook;

use Psr\Log\LoggerInterface;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Debug\DebuggableInterface;

final class LogDebugInfo
{
    public function __invoke(KernelInterface $kernel): void
    {
        if ($kernel->isDebug()) {
            /** @var LoggerInterface $logger */
            $logger = $kernel->getContainer()->get(LoggerInterface::class);

            assert($kernel instanceof DebuggableInterface);

            /** @var array<string, array<string, mixed>> $debug */
            $debug = $kernel->getDebugInfo();

            $context = $this->formatLogContext($debug);

            $logger->debug('kernel.debug', $context);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $debug
     *
     * @return array<string, mixed>
     */
    private function formatLogContext(array $debug): array
    {
        return [
            'boot_profile'      => $debug['bootProfile'],
            'request_profile'   => $debug['requestProfile'],
            'modules'           => $debug['modules'],
            'resolved_services' => $debug['services']['resolved'],
        ];
    }
}
