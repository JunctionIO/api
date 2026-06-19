<?php

namespace Junction\Api\Test\Event;

use PHPUnit\Framework\TestCase;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventSerializer;

final class EventSerializerTest extends TestCase
{
    public function test_serializes_event(): void
    {
        $event = new Event([
            'id'          => 'uuid-123',
            'name'        => 'test.event',
            'description' => 'A test event',
            'created_at'  => '2026-06-19 12:00:00',
            'updated_at'  => '2026-06-19 14:00:00',
        ]);

        $result = (new EventSerializer())->serialize($event);

        $this->assertSame('uuid-123', $result['id']);
        $this->assertSame('test.event', $result['name']);
        $this->assertSame('A test event', $result['description']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['updated_at']);
    }

    public function test_serializes_event_with_null_description(): void
    {
        $event = new Event([
            'id'         => 'uuid-123',
            'name'       => 'test.event',
            'created_at' => '2026-06-19 12:00:00',
            'updated_at' => '2026-06-19 14:00:00',
        ]);

        $result = (new EventSerializer())->serialize($event);

        $this->assertNull($result['description']);
    }
}
