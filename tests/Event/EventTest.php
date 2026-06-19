<?php

namespace Junction\Api\Test\Event;

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

}
