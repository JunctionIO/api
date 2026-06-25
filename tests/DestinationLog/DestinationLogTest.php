<?php

namespace Junction\Api\Test\DestinationLog;

use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\EventLog\EventLog;
use PHPUnit\Framework\TestCase;

final class DestinationLogTest extends TestCase
{
    public function test_id_getter(): void
    {
        $log = new DestinationLog(['id' => 'log-uuid']);

        $this->assertSame('log-uuid', $log->id);
    }

    public function test_trace_id_getter(): void
    {
        $log = new DestinationLog(['trace_id' => 'trace-abc']);

        $this->assertSame('trace-abc', $log->traceId);
    }

    public function test_trace_id_setter(): void
    {
        $log = new DestinationLog([]);

        $log->traceId = 'trace-abc';

        $this->assertSame('trace-abc', $log->traceId);
    }

    public function test_event_log_id_getter(): void
    {
        $log = new DestinationLog(['event_log_id' => 'event-log-uuid']);

        $this->assertSame('event-log-uuid', $log->eventLogId);
    }

    public function test_event_log_id_setter(): void
    {
        $log = new DestinationLog([]);

        $log->eventLogId = 'event-log-uuid';

        $this->assertSame('event-log-uuid', $log->eventLogId);
    }

    public function test_destination_id_getter(): void
    {
        $log = new DestinationLog(['destination_id' => 'dest-uuid']);

        $this->assertSame('dest-uuid', $log->destinationId);
    }

    public function test_destination_id_setter(): void
    {
        $log = new DestinationLog([]);

        $log->destinationId = 'dest-uuid';

        $this->assertSame('dest-uuid', $log->destinationId);
    }

    public function test_status_getter(): void
    {
        $log = new DestinationLog(['status' => 'pending']);

        $this->assertSame('pending', $log->status);
    }

    public function test_status_setter(): void
    {
        $log = new DestinationLog([]);

        $log->status = 'dispatched';

        $this->assertSame('dispatched', $log->status);
    }

    public function test_attempted_at_is_nullable(): void
    {
        $log = new DestinationLog([]);

        $this->assertNull($log->attemptedAt);
    }

    public function test_attempted_at_getter(): void
    {
        $log = new DestinationLog(['attempted_at' => '2026-06-25 10:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $log->attemptedAt);
    }

    public function test_attempted_at_setter(): void
    {
        $log = new DestinationLog([]);
        $now = new \DateTimeImmutable();

        $log->attemptedAt = $now;

        $this->assertSame($now->getTimestamp(), $log->attemptedAt->getTimestamp());
    }

    public function test_error_is_nullable(): void
    {
        $log = new DestinationLog([]);

        $this->assertNull($log->error);
    }

    public function test_error_getter(): void
    {
        $log = new DestinationLog(['error' => 'Connection refused']);

        $this->assertSame('Connection refused', $log->error);
    }

    public function test_error_setter(): void
    {
        $log = new DestinationLog([]);

        $log->error = 'timeout';

        $this->assertSame('timeout', $log->error);
    }

    public function test_created_at_getter(): void
    {
        $log = new DestinationLog(['created_at' => '2026-06-25 10:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $log->createdAt);
    }

    public function test_updated_at_getter(): void
    {
        $log = new DestinationLog(['updated_at' => '2026-06-25 10:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $log->updatedAt);
    }

    public function test_set_destination_and_get_destination(): void
    {
        $log         = new DestinationLog([]);
        $destination = new Destination(['id' => 'dest-uuid', 'name' => 'My Webhook']);

        $log->setDestination($destination);

        $this->assertSame($destination, $log->getDestination());
    }

    public function test_get_destination_throws_when_not_set(): void
    {
        $log = new DestinationLog([]);

        $this->expectException(\LogicException::class);

        $log->getDestination();
    }

    public function test_set_event_log_and_get_event_log(): void
    {
        $log      = new DestinationLog([]);
        $eventLog = new EventLog(['id' => 'event-log-uuid']);

        $log->setEventLog($eventLog);

        $this->assertSame($eventLog, $log->getEventLog());
    }

    public function test_get_event_log_throws_when_not_set(): void
    {
        $log = new DestinationLog([]);

        $this->expectException(\LogicException::class);

        $log->getEventLog();
    }
}
