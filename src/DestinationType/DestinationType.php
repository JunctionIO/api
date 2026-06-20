<?php

namespace Junction\Api\DestinationType;

use Meritum\Database\Model;

final class DestinationType extends Model
{
    protected string $table = 'destination_types';

    protected array $casts = [
        'config_schema' => 'json',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
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

    public string $queue {
        get {
            /** @var string */
            return $this->getAttribute('queue');
        }
        set {
            $this->setAttribute('queue', $value);
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

    /**
     * @var string[]
     */
    public array $configSchema {
        get {
            /** @var string[] */
            return $this->getAttribute('config_schema');
        }
        set {
            $this->setAttribute('config_schema', $value);
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
}
