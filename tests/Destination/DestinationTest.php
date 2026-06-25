<?php

namespace Junction\Api\Test\Destination;

use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class DestinationTest extends TestCase
{
    private function makeDestination(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'description'         => 'A test destination',
            'destination_type_id' => 'type-uuid',
            'config'              => ['url' => 'https://example.com/webhook'],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 11:00:00',
        ]);
    }

    public function test_id_getter(): void
    {
        $this->assertSame('dest-uuid', $this->makeDestination()->id);
    }

    public function test_name_getter(): void
    {
        $this->assertSame('My Webhook', $this->makeDestination()->name);
    }

    public function test_name_setter(): void
    {
        $destination       = $this->makeDestination();
        $destination->name = 'Updated Name';

        $this->assertSame('Updated Name', $destination->name);
    }

    public function test_description_getter(): void
    {
        $this->assertSame('A test destination', $this->makeDestination()->description);
    }

    public function test_description_is_nullable(): void
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

        $this->assertNull($destination->description);
    }

    public function test_description_setter(): void
    {
        $destination              = $this->makeDestination();
        $destination->description = 'New description';

        $this->assertSame('New description', $destination->description);
    }

    public function test_destination_type_id_getter(): void
    {
        $this->assertSame('type-uuid', $this->makeDestination()->destinationTypeId);
    }

    public function test_config_getter(): void
    {
        $this->assertSame(['url' => 'https://example.com/webhook'], $this->makeDestination()->config);
    }

    public function test_status_getter(): void
    {
        $this->assertSame('active', $this->makeDestination()->status);
    }

    public function test_status_setter(): void
    {
        $destination         = $this->makeDestination();
        $destination->status = 'inactive';

        $this->assertSame('inactive', $destination->status);
    }

    public function test_created_at_is_date_time(): void
    {
        $this->assertInstanceOf(\DateTimeInterface::class, $this->makeDestination()->createdAt);
    }

    public function test_updated_at_is_date_time(): void
    {
        $this->assertInstanceOf(\DateTimeInterface::class, $this->makeDestination()->updatedAt);
    }

    public function test_set_and_get_destination_type(): void
    {
        $destination = $this->makeDestination();
        $type        = new DestinationType(['id' => 'type-uuid', 'name' => 'http', 'queue' => 'junction.destinations.http']);

        $destination->setDestinationType($type);

        $this->assertSame($type, $destination->getDestinationType());
    }

    public function test_get_destination_type_throws_when_not_set(): void
    {
        $this->expectException(\LogicException::class);

        $this->makeDestination()->getDestinationType();
    }

    public function test_set_and_get_events(): void
    {
        $destination = $this->makeDestination();
        $events      = new Collection([
            'event-uuid' => new Event(['id' => 'event-uuid', 'name' => 'order.placed']),
        ]);

        $destination->setEvents($events);

        $this->assertSame($events, $destination->getEvents());
    }

    public function test_get_events_returns_null_when_not_set(): void
    {
        $this->assertNull($this->makeDestination()->getEvents());
    }
}
