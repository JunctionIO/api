<?php

namespace Junction\Api\Destination;

use Meritum\Database\Model;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use Junction\Api\DestinationType\DestinationType;

final class Destination extends Model
{
    protected string $table = 'destinations';

    protected array $casts = [
        'config'     => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public string $id {
        get {
            /** @var string */
            return $this->getAttribute('id');
        }
    }

    public string $name {
        get {
            /** @var string */
            return $this->getAttribute('name');
        }
        set {
            $this->setAttribute('name', $value);
        }
    }

    public ?string $description {
        get {
            /** @var string|null */
            return $this->getAttribute('description');
        }
        set {
            $this->setAttribute('description', $value);
        }
    }

    public string $destinationTypeId {
        get {
            /** @var string */
            return $this->getAttribute('destination_type_id');
        }
        set {
            $this->setAttribute('destination_type_id', $value);
        }
    }

    /**
     * @var array<string, mixed>
     */
    public array $config {
        get {
            /** @var array<string, mixed> */
            return $this->getAttribute('config');
        }
        set {
            $this->setAttribute('config', $value);
        }
    }

    public string $status {
        get {
            /** @var string */
            return $this->getAttribute('status');
        }
        set {
            $this->setAttribute('status', $value);
        }
    }

    public \DateTimeInterface $createdAt {
        get {
            /** @var \DateTimeInterface */
            return $this->getAttribute('created_at');
        }
    }

    public \DateTimeInterface $updatedAt {
        get {
            /** @var \DateTimeInterface */
            return $this->getAttribute('updated_at');
        }
    }

    public function setDestinationType(DestinationType $type): void
    {
        $this->setRelation('destination_type', $type);
    }

    public function getDestinationType(): DestinationType
    {
        if (false === $this->hasRelation('destination_type')) {
            throw new \LogicException('Relation destination_type has not been set');
        }

        /** @var DestinationType */
        return $this->getRelation('destination_type');
    }

    /**
     * @param Collection<Event> $events
     */
    public function setEvents(Collection $events): void
    {
        $this->setRelation('events', $events);
    }

    /**
     * @return Collection<Event>|null
     */
    public function getEvents(): ?Collection
    {
        /** @var Collection<Event>|null */
        return $this->getRelation('events');
    }
}
