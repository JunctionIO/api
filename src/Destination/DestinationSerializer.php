<?php

namespace Junction\Api\Destination;

use Meritum\Serialization\SerializerInterface;

final class DestinationSerializer implements SerializerInterface
{
    /**
     * @param Destination $data
     */
    public function serialize(mixed $data): array
    {
        $type = $data->getDestinationType();

        return [
            'id'               => $data->id,
            'name'             => $data->name,
            'description'      => $data->description,
            'destination_type' => [
                'id'   => $type->id,
                'name' => $type->name,
            ],
            'events'           => $this->getEvents($data),
            'config'           => $data->config,
            'status'           => $data->status,
            'created_at'       => $data->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'updated_at'       => $data->updatedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
        ];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function getEvents(Destination $model): array
    {
        $events = $model->getEvents();

        if (null === $events) {
            throw new \LogicException('The events relation must be set');
        }

        $return = [];

        foreach ($events as $event) {
            $return[] = ['id' => $event->id, 'name' => $event->name];
        }

        return $return;
    }
}
