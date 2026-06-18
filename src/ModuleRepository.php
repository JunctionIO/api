<?php

namespace Junction\Api;

use Georgeff\Kernel\Environment;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Kernel\Module\ModuleRepositoryInterface;

final class ModuleRepository implements ModuleRepositoryInterface
{
    /**
     * Register application modules
     *
     * @return ModuleInterface[]
     */
    public function modules(Environment $env): array
    {
        return [
            new AppModule(),
            new \Meritum\Database\DatabaseModule(),
            new \Meritum\Validation\ValidationModule(),
            new \Meritum\Logger\LoggerModule(),
            new \Meritum\StructuredLogging\StructuredLoggingModule(),
            new \Meritum\Serialization\SerializationModule(),
            new \Meritum\HttpExceptionHandler\ExceptionHandlerModule(),
            new \Meritum\BusModule\BusModule(),
        ];
    }
}
