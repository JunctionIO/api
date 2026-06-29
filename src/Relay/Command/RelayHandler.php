<?php

namespace Junction\Api\Relay\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\Relay\MessageEnvelope;
use Junction\Api\DestinationLog\Command\CreateMany;

final class RelayHandler
{
    public function __construct(
        private readonly QueueInterface $queue,
        private readonly DispatcherInterface $dispatcher
    ) {}

    public function __invoke(Relay $command): void
    {
        if ($command->destinations->isNotEmpty()) {
            $destinationIds = [];

            foreach ($command->destinations as $destination) {
                $message = new MessageEnvelope(
                    $command->payload,
                    $command->traceId,
                    $command->logId,
                    $destination
                );

                $this->queue->publish($destination->getDestinationType()->queue, $message);

                $destinationIds[] = $destination->id;
            }

            $this->dispatcher->dispatch(new CreateMany(
                $command->traceId,
                $command->logId,
                $destinationIds
            ));
        }
    }
}
