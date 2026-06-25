<?php

namespace Junction\Api\DestinationLog;

use Meritum\Serialization\SerializerInterface;

final class DestinationLogSerializer implements SerializerInterface
{
    /**
     * @param DestinationLog $data
     */
    public function serialize(mixed $data): array
    {
        $eventLog    = $data->getEventLog();
        $destination = $data->getDestination();

        return [
            'id'           => $data->id,
            'trace_id'     => $data->traceId,
            'status'       => $data->status,
            'destination'  => [
                'id'          => $destination->id,
                'name'        => $destination->name,
                'description' => $destination->description,
            ],
            'event_log'    => [
                'id'          => $eventLog->id,
                'payload'     => $eventLog->payload,
                'source_ip'   => $eventLog->sourceIp,
                'received_at' => $eventLog->receivedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            ],
            'attempted_at' => null !== $data->attemptedAt ? $data->attemptedAt->format(\DateTimeInterface::RFC3339_EXTENDED) : null,
            'error'        => $data->error,
            'created_at'   => $data->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'updated_at'   => $data->updatedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
        ];
    }
}
