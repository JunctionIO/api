<?php

namespace Junction\Api\Event;

use Meritum\Database\Model;

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

    public \DateTimeImmutable $createdAt {
        get {
            /** @var \DateTimeImmutable */
            return $this->getAttribute('created_at');
        }
    }

    public \DateTimeImmutable $updatedAt {
        get {
            /** @var \DateTimeImmutable */
            return $this->getAttribute('updated_at');
        }
    }
}
