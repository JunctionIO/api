<?php

namespace Junction\Api\DestinationLog;

use Meritum\Database\Model;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Destination\Destination;

final class DestinationLog extends Model
{
    protected string $table = 'event_log_destinations';

    protected array $casts = [
        'attempted_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
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

    public string $eventLogId {
        get {
            /** @var string */
            return $this->getAttribute('event_log_id');
        }
        set {
            $this->setAttribute('event_log_id', $value);
        }
    }

    public string $destinationId {
        get {
            /** @var string */
            return $this->getAttribute('destination_id');
        }
        set {
            $this->setAttribute('destination_id', $value);
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

    public ?\DateTimeInterface $attemptedAt {
        get {
            /** @var \DateTimeInterface|null */
            return $this->getAttribute('attempted_at');
        }
        set {
            $this->setAttribute('attempted_at', $value);
        }
    }

    public ?string $error {
        get {
            /** @var string|null */
            return $this->getAttribute('error');
        }
        set {
            $this->setAttribute('error', $value);
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
     * @throws \LogicException
     */
    public function getDestination(): Destination
    {
        if (false === $this->hasRelation('destination')) {
            throw new \LogicException('Relation destination has not been set');
        }

        /** @var Destination */
        return $this->getRelation('destination');
    }

    public function setDestination(Destination $destination): void
    {
        $this->setRelation('destination', $destination);
    }

    /**
     * @throws \LogicException
     */
    public function getEventLog(): EventLog
    {
        if (false === $this->hasRelation('event_log')) {
            throw new \LogicException('Relation event_log has not been set');
        }

        /** @var EventLog */
        return $this->getRelation('event_log');
    }

    public function setEventLog(EventLog $log): void
    {
        $this->setRelation('event_log', $log);
    }
}
