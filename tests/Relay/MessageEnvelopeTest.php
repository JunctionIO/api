<?php

namespace Junction\Api\Test\Relay;

use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Relay\MessageEnvelope;
use PHPUnit\Framework\TestCase;

final class MessageEnvelopeTest extends TestCase
{
    private function makeDestination(string $typeName = 'http', array $config = ['url' => 'https://example.com']): Destination
    {
        $type = new DestinationType([
            'id'            => 'type-uuid',
            'name'          => $typeName,
            'queue'         => $typeName,
            'config_schema' => [],
            'created_at'    => '2026-06-23 10:00:00',
            'updated_at'    => '2026-06-23 10:00:00',
        ]);

        $dest = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => $config,
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);

        $dest->setDestinationType($type);

        return $dest;
    }

    public function test_stores_payload(): void
    {
        $envelope = new MessageEnvelope(['foo' => 'bar'], 'trace-id', 'log-id', $this->makeDestination());

        $this->assertSame(['foo' => 'bar'], $envelope->payload);
    }

    public function test_meta_contains_trace_id(): void
    {
        $envelope = new MessageEnvelope([], 'my-trace-id', 'log-id', $this->makeDestination());

        $this->assertSame('my-trace-id', $envelope->meta['trace_id']);
    }

    public function test_meta_contains_log_id(): void
    {
        $envelope = new MessageEnvelope([], 'trace-id', 'my-log-id', $this->makeDestination());

        $this->assertSame('my-log-id', $envelope->meta['log_id']);
    }

    public function test_meta_contains_destination_type(): void
    {
        $dest     = $this->makeDestination('http');
        $envelope = new MessageEnvelope([], 'trace-id', 'log-id', $dest);

        $this->assertSame('http', $envelope->meta['destination']['type']);
    }

    public function test_meta_contains_destination_config(): void
    {
        $config   = ['url' => 'https://example.com', 'secret' => 'abc123'];
        $dest     = $this->makeDestination(config: $config);
        $envelope = new MessageEnvelope([], 'trace-id', 'log-id', $dest);

        $this->assertSame($config, $envelope->meta['destination']['config']);
    }

    public function test_json_serialize_includes_payload(): void
    {
        $payload  = ['event' => 'order.placed', 'amount' => 99];
        $envelope = new MessageEnvelope($payload, 'trace-id', 'log-id', $this->makeDestination());

        $this->assertSame($payload, $envelope->jsonSerialize()['payload']);
    }

    public function test_json_serialize_includes_meta(): void
    {
        $envelope = new MessageEnvelope([], 'trace-id', 'log-id', $this->makeDestination());

        $this->assertSame($envelope->meta, $envelope->jsonSerialize()['meta']);
    }

    public function test_json_encode_produces_valid_json(): void
    {
        $envelope = new MessageEnvelope(['key' => 'val'], 'trace-id', 'log-id', $this->makeDestination());

        $json = json_encode($envelope);

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('payload', $decoded);
        $this->assertArrayHasKey('meta', $decoded);
    }

    public function test_json_encoded_meta_contains_expected_keys(): void
    {
        $envelope = new MessageEnvelope([], 'trace-id', 'log-id', $this->makeDestination());

        $decoded = json_decode(json_encode($envelope), true);

        $this->assertArrayHasKey('trace_id', $decoded['meta']);
        $this->assertArrayHasKey('log_id', $decoded['meta']);
        $this->assertArrayHasKey('destination', $decoded['meta']);
    }
}
