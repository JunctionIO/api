<?php

namespace Junction\Api\Test\Unit\Destination;

use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationSerializer;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class DestinationSerializerTest extends TestCase
{
    private function makeDestination(): Destination
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'description'         => 'A test destination',
            'destination_type_id' => 'type-uuid',
            'config'              => ['url' => 'https://example.com/webhook'],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 11:00:00',
        ]);

        $destination->setDestinationType(
            new DestinationType(['id' => 'type-uuid', 'name' => 'http', 'queue' => 'junction.destinations.http'])
        );

        $destination->setEvents(new Collection([]));

        return $destination;
    }

    public function test_serializes_all_fields(): void
    {
        $result = (new DestinationSerializer())->serialize($this->makeDestination());

        $this->assertSame('dest-uuid', $result['id']);
        $this->assertSame('My Webhook', $result['name']);
        $this->assertSame('A test destination', $result['description']);
        $this->assertSame(['url' => 'https://example.com/webhook'], $result['config']);
        $this->assertSame('active', $result['status']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/', $result['updated_at']);
    }

    public function test_serializes_null_description(): void
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 11:00:00',
        ]);
        $destination->setDestinationType(
            new DestinationType(['id' => 'type-uuid', 'name' => 'http', 'queue' => 'junction.destinations.http'])
        );
        $destination->setEvents(new Collection([]));

        $result = (new DestinationSerializer())->serialize($destination);

        $this->assertNull($result['description']);
    }

    public function test_embeds_destination_type_id_and_name(): void
    {
        $result = (new DestinationSerializer())->serialize($this->makeDestination());

        $this->assertSame(['id' => 'type-uuid', 'name' => 'http'], $result['destination_type']);
    }

    public function test_embeds_events_as_array(): void
    {
        $destination = $this->makeDestination();
        $destination->setEvents(new Collection([
            'event-uuid-1' => new Event(['id' => 'event-uuid-1', 'name' => 'order.placed']),
            'event-uuid-2' => new Event(['id' => 'event-uuid-2', 'name' => 'order.shipped']),
        ]));

        $result = (new DestinationSerializer())->serialize($destination);

        $this->assertSame([
            ['id' => 'event-uuid-1', 'name' => 'order.placed'],
            ['id' => 'event-uuid-2', 'name' => 'order.shipped'],
        ], $result['events']);
    }

    public function test_embeds_empty_events_array_when_no_subscriptions(): void
    {
        $result = (new DestinationSerializer())->serialize($this->makeDestination());

        $this->assertSame([], $result['events']);
    }

    public function test_throws_when_events_relation_not_set(): void
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 11:00:00',
        ]);
        $destination->setDestinationType(
            new DestinationType(['id' => 'type-uuid', 'name' => 'http', 'queue' => 'junction.destinations.http'])
        );

        $this->expectException(\LogicException::class);

        (new DestinationSerializer())->serialize($destination);
    }

    public function test_throws_when_destination_type_not_set(): void
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 11:00:00',
        ]);

        $this->expectException(\LogicException::class);

        (new DestinationSerializer())->serialize($destination);
    }
}
