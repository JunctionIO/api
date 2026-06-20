<?php

namespace Junction\Api\DestinationType;

use Meritum\Serialization\SerializerInterface;

final class DestinationTypeSerializer implements SerializerInterface
{
    /**
     * @param DestinationType $data
     */
    public function serialize(mixed $data): array
    {
        return [
            'id'            => $data->id,
            'name'          => $data->name,
            'queue'         => $data->queue,
            'description'   => $data->description,
            'config_schema' => $data->configSchema,
            'created_at'    => $data->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'updated_at'    => $data->updatedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
        ];
    }
}
