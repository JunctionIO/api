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
            new Bus\BusModule(),
            new Http\HttpModule(),
            new Trace\TraceModule(),
            new Queue\QueueModule(),
            new Event\EventModule(),
            new Context\ContextModule(),
            new EventLog\EventLogModule(),
            new Exception\ExceptionModule(),
            new Validation\ValidationModule(),
            new Destination\DestinationModule(),
            new DestinationLog\DestinationLogModule(),
            new DestinationType\DestinationTypeModule(),
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
