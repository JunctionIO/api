<?php

namespace Junction\Api\Test\Unit\Event\Command;

use PHPUnit\Framework\TestCase;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Event\Command\FindOrCreate;
use Junction\Api\Event\Command\FindOrCreateHandler;

final class FindOrCreateHandlerTest extends TestCase
{
    public function test_returns_existing_event_when_found(): void
    {
        $existing = new Event(['id' => 'uuid-1', 'name' => 'test.event']);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findByName')->with('test.event')->willReturn($existing);
        $repo->expects($this->never())->method('save');

        $result = (new FindOrCreateHandler($repo))(new FindOrCreate('test.event'));

        $this->assertSame($existing, $result);
    }

    public function test_creates_and_returns_new_event_when_not_found(): void
    {
        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findByName')->with('test.event')->willReturn(null);
        $repo->expects($this->once())->method('save');

        $result = (new FindOrCreateHandler($repo))(new FindOrCreate('test.event', 'A test event'));

        $this->assertInstanceOf(Event::class, $result);
        $this->assertSame('test.event', $result->name);
        $this->assertSame('A test event', $result->description);
    }

    public function test_creates_event_with_null_description_when_not_provided(): void
    {
        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findByName')->willReturn(null);
        $repo->method('save')->willReturn(true);

        $result = (new FindOrCreateHandler($repo))(new FindOrCreate('test.event'));

        $this->assertNull($result->description);
    }
}
