<?php

namespace Junction\Api\Relay\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\Relay\MessageEnvelope;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\Command\CreateMany;

final class RelayHandler
{
    public function __construct(
        private readonly QueueInterface $queue,
        private readonly DispatcherInterface $dispatcher
    ) {}

    public function __invoke(Relay $command): void
    {
        if ($command->destinations->isEmpty()) {
            return;
        }

        $destinationIds = [];

        foreach ($command->destinations as $destination) {
            $destinationIds[] = $destination->id;
        }

        /** @var iterable<DestinationLog> $logs */
        $logs = $this->dispatcher->dispatch(new CreateMany(
            $command->traceId,
            $command->logId,
            $destinationIds
        ));

        $logByDestination = [];

        foreach ($logs as $log) {
            $logByDestination[$log->destinationId] = $log;
        }

        foreach ($command->destinations as $destination) {
            $message = new MessageEnvelope(
                $command->payload,
                $command->traceId,
                $logByDestination[$destination->id]->id,
                $destination
            );

            $this->queue->publish($destination->getDestinationType()->queue, $message);
        }
    }
}
