<?php

namespace Junction\Api\Test\Unit\EventLog;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\EventLogSerializer;
use PHPUnit\Framework\TestCase;

final class EventLogSerializerTest extends TestCase
{
    public function test_serializes_event_log(): void
    {
        $log = new EventLog([
            'id'          => 'log-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => ['foo' => 'bar'],
            'source_ip'   => '127.0.0.1',
            'auth_id'     => 'token-abc',
            'received_at' => '2026-06-21 12:00:00',
            'created_at'  => '2026-06-21 12:00:01',
        ]);

        $log->setEvent(new Event(['id' => 'event-uuid', 'name' => 'do.something']));

        $result = (new EventLogSerializer())->serialize($log);

        $this->assertSame('log-uuid', $result['id']);
        $this->assertSame('trace-uuid', $result['trace_id']);
        $this->assertSame('token-abc', $result['auth_id']);
        $this->assertSame('127.0.0.1', $result['source_ip']);
        $this->assertSame(['foo' => 'bar'], $result['payload']);
        $this->assertSame('event-uuid', $result['event']['id']);
        $this->assertSame('do.something', $result['event']['name']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['received_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['created_at']);
    }

    public function test_serializes_with_null_source_ip(): void
    {
        $log = new EventLog([
            'id'          => 'log-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => [],
            'auth_id'     => 'token-abc',
            'received_at' => '2026-06-21 12:00:00',
            'created_at'  => '2026-06-21 12:00:01',
        ]);

        $log->setEvent(new Event(['id' => 'event-uuid', 'name' => 'do.something']));

        $result = (new EventLogSerializer())->serialize($log);

        $this->assertNull($result['source_ip']);
    }

    public function test_event_id_is_not_in_output(): void
    {
        $log = new EventLog([
            'id'          => 'log-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => [],
            'auth_id'     => 'token-abc',
            'received_at' => '2026-06-21 12:00:00',
            'created_at'  => '2026-06-21 12:00:01',
        ]);

        $log->setEvent(new Event(['id' => 'event-uuid', 'name' => 'do.something']));

        $result = (new EventLogSerializer())->serialize($log);

        $this->assertArrayNotHasKey('event_id', $result);
    }
}
