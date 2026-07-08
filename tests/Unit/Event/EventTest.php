<?php

namespace Junction\Api\Test\Unit\Event;

use Junction\Api\Destination\Destination;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;
use Junction\Api\Event\Event;

final class EventTest extends TestCase
{
    public function test_id_getter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);

        $this->assertSame('uuid-123', $event->id);
    }

    public function test_name_getter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);

        $this->assertSame('test.event', $event->name);
    }

    public function test_name_setter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);

        $event->name = 'updated.event';

        $this->assertSame('updated.event', $event->name);
    }

    public function test_description_is_nullable(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);

        $this->assertNull($event->description);
    }

    public function test_description_getter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event', 'description' => 'A test event']);

        $this->assertSame('A test event', $event->description);
    }

    public function test_description_setter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event', 'description' => 'A test event']);

        $event->description = 'Updated description';

        $this->assertSame('Updated description', $event->description);
    }

    public function test_created_at_getter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event', 'created_at' => '2026-06-19 12:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $event->createdAt);
    }

    public function test_updated_at_getter(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event', 'updated_at' => '2026-06-19 14:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $event->updatedAt);
    }

    public function test_set_and_get_destinations(): void
    {
        $event        = new Event(['id' => 'uuid-123', 'name' => 'test.event']);
        $destinations = new Collection([]);

        $event->setDestinations($destinations);

        $this->assertSame($destinations, $event->getDestinations());
    }

    public function test_get_destinations_throws_when_not_set(): void
    {
        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);

        $this->expectException(\LogicException::class);

        $event->getDestinations();
    }

    public function test_get_destinations_returns_assigned_collection(): void
    {
        $destination = new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
        $destinations = new Collection(['dest-uuid' => $destination]);

        $event = new Event(['id' => 'uuid-123', 'name' => 'test.event']);
        $event->setDestinations($destinations);

        $this->assertSame($destination, $event->getDestinations()->get('dest-uuid'));
    }

}
