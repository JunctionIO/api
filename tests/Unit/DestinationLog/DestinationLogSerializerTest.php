<?php

namespace Junction\Api\Test\Unit\DestinationLog;

use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogSerializer;
use Junction\Api\EventLog\EventLog;
use PHPUnit\Framework\TestCase;

final class DestinationLogSerializerTest extends TestCase
{
    private function makeDestination(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'description'         => 'A test destination',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-25 10:00:00',
            'updated_at'          => '2026-06-25 10:00:00',
        ]);
    }

    private function makeEventLog(): EventLog
    {
        return new EventLog([
            'id'          => 'event-log-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => ['order_id' => 123],
            'source_ip'   => '127.0.0.1',
            'auth_id'     => 'token-abc',
            'received_at' => '2026-06-25 10:00:00',
            'created_at'  => '2026-06-25 10:00:00',
        ]);
    }

    private function makeLog(): DestinationLog
    {
        $log = new DestinationLog([
            'id'             => 'dest-log-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'event-log-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'dispatched',
            'attempted_at'   => '2026-06-25 10:00:01',
            'error'          => null,
            'created_at'     => '2026-06-25 10:00:00',
            'updated_at'     => '2026-06-25 10:00:01',
        ]);

        $log->setDestination($this->makeDestination());
        $log->setEventLog($this->makeEventLog());

        return $log;
    }

    public function test_serializes_all_scalar_fields(): void
    {
        $result = (new DestinationLogSerializer())->serialize($this->makeLog());

        $this->assertSame('dest-log-uuid', $result['id']);
        $this->assertSame('trace-uuid', $result['trace_id']);
        $this->assertSame('dispatched', $result['status']);
        $this->assertNull($result['error']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['attempted_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['updated_at']);
    }

    public function test_embeds_destination_id_name_description(): void
    {
        $result = (new DestinationLogSerializer())->serialize($this->makeLog());

        $this->assertSame('dest-uuid', $result['destination']['id']);
        $this->assertSame('My Webhook', $result['destination']['name']);
        $this->assertSame('A test destination', $result['destination']['description']);
    }

    public function test_embeds_null_destination_description(): void
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-25 10:00:00',
            'updated_at'          => '2026-06-25 10:00:00',
        ]);

        $log = $this->makeLog();
        $log->setDestination($destination);

        $result = (new DestinationLogSerializer())->serialize($log);

        $this->assertNull($result['destination']['description']);
    }

    public function test_embeds_event_log_fields(): void
    {
        $result = (new DestinationLogSerializer())->serialize($this->makeLog());

        $this->assertSame('event-log-uuid', $result['event_log']['id']);
        $this->assertSame(['order_id' => 123], $result['event_log']['payload']);
        $this->assertSame('127.0.0.1', $result['event_log']['source_ip']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['event_log']['received_at']);
    }

    public function test_embeds_null_event_log_source_ip(): void
    {
        $eventLog = new EventLog([
            'id'          => 'event-log-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => [],
            'auth_id'     => 'token-abc',
            'received_at' => '2026-06-25 10:00:00',
            'created_at'  => '2026-06-25 10:00:00',
        ]);

        $log = $this->makeLog();
        $log->setEventLog($eventLog);

        $result = (new DestinationLogSerializer())->serialize($log);

        $this->assertNull($result['event_log']['source_ip']);
    }

    public function test_serializes_null_attempted_at(): void
    {
        $log = new DestinationLog([
            'id'             => 'dest-log-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'event-log-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'pending',
            'created_at'     => '2026-06-25 10:00:00',
            'updated_at'     => '2026-06-25 10:00:00',
        ]);

        $log->setDestination($this->makeDestination());
        $log->setEventLog($this->makeEventLog());

        $result = (new DestinationLogSerializer())->serialize($log);

        $this->assertNull($result['attempted_at']);
    }

    public function test_serializes_error_message(): void
    {
        $log = $this->makeLog();
        $log->error = 'Connection refused';

        $result = (new DestinationLogSerializer())->serialize($log);

        $this->assertSame('Connection refused', $result['error']);
    }

    public function test_throws_when_destination_not_set(): void
    {
        $log = new DestinationLog([
            'id'             => 'dest-log-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'event-log-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'pending',
            'created_at'     => '2026-06-25 10:00:00',
            'updated_at'     => '2026-06-25 10:00:00',
        ]);

        $log->setEventLog($this->makeEventLog());

        $this->expectException(\LogicException::class);

        (new DestinationLogSerializer())->serialize($log);
    }

    public function test_throws_when_event_log_not_set(): void
    {
        $log = new DestinationLog([
            'id'             => 'dest-log-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'event-log-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'pending',
            'created_at'     => '2026-06-25 10:00:00',
            'updated_at'     => '2026-06-25 10:00:00',
        ]);

        $log->setDestination($this->makeDestination());

        $this->expectException(\LogicException::class);

        (new DestinationLogSerializer())->serialize($log);
    }
}
