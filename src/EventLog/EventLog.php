<?php

namespace Junction\Api\EventLog;

use Meritum\Database\Model;
use Junction\Api\Event\Event;

final class EventLog extends Model
{
    protected string $table = 'event_logs';

    protected bool $timestamps = false;

    protected array $casts = [
        'payload'     => 'json',
        'received_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public string $id {
        get {
            /** @var string */
            return $this->getAttribute('id');
        }
    }

    public string $traceId {
        get {
            /** @var string */
            return $this->getAttribute('trace_id');
        }
        set {
            $this->setAttribute('trace_id', $value);
        }
    }

    public string $eventId {
        get {
            /** @var string */
            return $this->getAttribute('event_id');
        }
        set {
            $this->setAttribute('event_id', $value);
        }
    }

    /**
     * @var array<mixed>
     */
    public array $payload {
        get {
            /** @var array<mixed> */
            return $this->getAttribute('payload');
        }
        set {
            $this->setAttribute('payload', $value);
        }
    }

    public ?string $sourceIp {
        get {
            /** @var string|null */
            return $this->getAttribute('source_ip');
        }
        set {
            $this->setAttribute('source_ip', $value);
        }
    }

    public string $authId {
        get {
            /** @var string */
            return $this->getAttribute('auth_id');
        }
        set {
            $this->setAttribute('auth_id', $value);
        }
    }

    public \DateTimeInterface $receivedAt {
        get {
            /** @var \DateTimeInterface */
            return $this->getAttribute('received_at');
        }
        set {
            $this->setAttribute('received_at', $value);
        }
    }

    public \DateTimeInterface $createdAt {
        get {
            /** @var \DateTimeInterface */
            return $this->getAttribute('created_at');
        }
    }

    public function initCreatedAt(): void
    {
        $createdAt = $this->getAttribute('created_at');

        if (null === $createdAt) {
            $now = new \DateTimeImmutable();

            $this->setAttribute('created_at', $now);
        }
    }

    public function setEvent(Event $event): void
    {
        $this->setRelation('event', $event);
    }

    /**
     * @throws \LogicException
     */
    public function getEvent(): Event
    {
        if (false === $this->hasRelation('event')) {
            throw new \LogicException('Related event has not been set.');
        }

        /** @var Event */
        return $this->getRelation('event');
    }
}
