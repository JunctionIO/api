<?php

namespace Junction\Api\Test\Unit\EventLog;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use PHPUnit\Framework\TestCase;

final class EventLogTest extends TestCase
{
    public function test_id_getter(): void
    {
        $log = new EventLog(['id' => 'uuid-123']);

        $this->assertSame('uuid-123', $log->id);
    }

    public function test_trace_id_getter(): void
    {
        $log = new EventLog(['trace_id' => 'trace-abc']);

        $this->assertSame('trace-abc', $log->traceId);
    }

    public function test_trace_id_setter(): void
    {
        $log = new EventLog([]);

        $log->traceId = 'trace-abc';

        $this->assertSame('trace-abc', $log->traceId);
    }

    public function test_event_id_getter(): void
    {
        $log = new EventLog(['event_id' => 'event-456']);

        $this->assertSame('event-456', $log->eventId);
    }

    public function test_event_id_setter(): void
    {
        $log = new EventLog([]);

        $log->eventId = 'event-456';

        $this->assertSame('event-456', $log->eventId);
    }

    public function test_payload_getter(): void
    {
        $log = new EventLog(['payload' => ['key' => 'value']]);

        $this->assertSame(['key' => 'value'], $log->payload);
    }

    public function test_payload_setter(): void
    {
        $log = new EventLog([]);

        $log->payload = ['foo' => 'bar'];

        $this->assertSame(['foo' => 'bar'], $log->payload);
    }

    public function test_payload_accepts_indexed_array(): void
    {
        $log = new EventLog([]);

        $log->payload = ['a', 'b', 'c'];

        $this->assertSame(['a', 'b', 'c'], $log->payload);
    }

    public function test_source_ip_is_nullable(): void
    {
        $log = new EventLog([]);

        $this->assertNull($log->sourceIp);
    }

    public function test_source_ip_getter(): void
    {
        $log = new EventLog(['source_ip' => '127.0.0.1']);

        $this->assertSame('127.0.0.1', $log->sourceIp);
    }

    public function test_source_ip_setter(): void
    {
        $log = new EventLog([]);

        $log->sourceIp = '192.168.1.1';

        $this->assertSame('192.168.1.1', $log->sourceIp);
    }

    public function test_auth_id_getter(): void
    {
        $log = new EventLog(['auth_id' => 'token-789']);

        $this->assertSame('token-789', $log->authId);
    }

    public function test_auth_id_setter(): void
    {
        $log = new EventLog([]);

        $log->authId = 'token-789';

        $this->assertSame('token-789', $log->authId);
    }

    public function test_received_at_getter(): void
    {
        $log = new EventLog(['received_at' => '2026-06-21 12:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $log->receivedAt);
    }

    public function test_received_at_setter(): void
    {
        $log = new EventLog([]);
        $now = new \DateTimeImmutable();

        $log->receivedAt = $now;

        $this->assertSame($now->getTimestamp(), $log->receivedAt->getTimestamp());
    }

    public function test_init_created_at_sets_timestamp_when_not_set(): void
    {
        $log = new EventLog([]);

        $log->initCreatedAt();

        $this->assertInstanceOf(\DateTimeInterface::class, $log->createdAt);
    }

    public function test_init_created_at_is_noop_when_already_set(): void
    {
        $original = new \DateTimeImmutable('2026-01-01 00:00:00');
        $log = new EventLog(['created_at' => $original]);

        $log->initCreatedAt();

        $this->assertSame($original->getTimestamp(), $log->createdAt->getTimestamp());
    }

    public function test_set_event_and_get_event(): void
    {
        $log = new EventLog([]);
        $event = new Event(['id' => 'event-uuid', 'name' => 'do.something']);

        $log->setEvent($event);

        $this->assertSame('event-uuid', $log->getEvent()->id);
        $this->assertSame('do.something', $log->getEvent()->name);
    }

    public function test_get_event_throws_when_not_set(): void
    {
        $log = new EventLog([]);

        $this->expectException(\LogicException::class);

        $log->getEvent();
    }
}
