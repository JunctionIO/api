<?php

namespace Junction\Api\Test\DestinationType;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeSerializer;
use PHPUnit\Framework\TestCase;

final class DestinationTypeSerializerTest extends TestCase
{
    public function test_serializes_destination_type(): void
    {
        $type = new DestinationType([
            'id'            => 'uuid-123',
            'name'          => 'http',
            'queue'         => 'http_queue',
            'description'   => 'HTTP destination',
            'config_schema' => ['url', 'method'],
            'created_at'    => '2026-06-20 12:00:00',
            'updated_at'    => '2026-06-20 14:00:00',
        ]);

        $result = (new DestinationTypeSerializer())->serialize($type);

        $this->assertSame('uuid-123', $result['id']);
        $this->assertSame('http', $result['name']);
        $this->assertSame('http_queue', $result['queue']);
        $this->assertSame('HTTP destination', $result['description']);
        $this->assertSame(['url', 'method'], $result['config_schema']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['updated_at']);
    }

    public function test_serializes_with_null_description(): void
    {
        $type = new DestinationType([
            'id'            => 'uuid-123',
            'name'          => 'http',
            'queue'         => 'http_queue',
            'config_schema' => [],
            'created_at'    => '2026-06-20 12:00:00',
            'updated_at'    => '2026-06-20 14:00:00',
        ]);

        $result = (new DestinationTypeSerializer())->serialize($type);

        $this->assertNull($result['description']);
    }
}
