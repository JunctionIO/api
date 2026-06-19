<?php

namespace Junction\Api\Event;

use Meritum\Serialization\SerializerInterface;

final class EventSerializer implements SerializerInterface
{
    /**
     * @param Event $data
     */
    public function serialize(mixed $data): array
    {
        return [
            'id'          => $data->id,
            'name'        => $data->name,
            'description' => $data->description,
            'created_at'  => $data->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'updated_at'  => $data->updatedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
        ];
    }
}
