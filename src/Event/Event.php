<?php

namespace Junction\Api\Event;

use Meritum\Database\Model;
use Meritum\Database\Support\Collection;
use Junction\Api\Destination\Destination;

final class Event extends Model
{
    protected string $table = 'events';

    protected array $casts = [
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

    /**
     * @param Collection<Destination> $destinations
     */
    public function setDestinations(Collection $destinations): void
    {
        $this->setRelation('destinations', $destinations);
    }

    /**
     * @return Collection<Destination>
     */
    public function getDestinations(): Collection
    {
        if (false === $this->hasRelation('destinations')) {
            throw new \LogicException('Relation destinations has not been set');
        }

        /** @var Collection<Destination> */
        return $this->getRelation('destinations');
    }
}
