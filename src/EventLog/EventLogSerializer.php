<?php

namespace Junction\Api\EventLog;

use Meritum\Serialization\SerializerInterface;

final class EventLogSerializer implements SerializerInterface
{
    /**
     * @param EventLog $data
     */
    public function serialize(mixed $data): array
    {
        $event = $data->getEvent();

        return [
            'id'          => $data->id,
            'trace_id'    => $data->traceId,
            'auth_id'     => $data->authId,
            'source_ip'   => $data->sourceIp,
            'payload'     => $data->payload,
            'event'       => [
                'id'   => $event->id,
                'name' => $event->name,
            ],
            'received_at' => $data->receivedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'created_at'  => $data->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
        ];
    }
}
