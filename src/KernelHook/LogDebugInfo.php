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

            $logger->debug('kernel.debug', ['kernel_debugInfo' => $kernel->getDebugInfo()]);
        }
    }
}
