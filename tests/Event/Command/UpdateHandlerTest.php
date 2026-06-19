<?php

namespace Junction\Api\Test\Event\Command;

use PHPUnit\Framework\TestCase;
use Meritum\Database\Exception\ModelNotFoundException;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Event\Command\Update;
use Junction\Api\Event\Command\UpdateHandler;

final class UpdateHandlerTest extends TestCase
{
    public function test_updates_description_and_returns_event(): void
    {
        $event = new Event(['id' => 'uuid-1', 'name' => 'test.event', 'description' => 'Old description']);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findOrFail')->with('uuid-1')->willReturn($event);
        $repo->expects($this->once())->method('save')->with($event);

        $result = (new UpdateHandler($repo))(new Update('uuid-1', 'New description'));

        $this->assertSame($event, $result);
        $this->assertSame('New description', $result->description);
    }

    public function test_clears_description_when_null(): void
    {
        $event = new Event(['id' => 'uuid-1', 'name' => 'test.event', 'description' => 'Old description']);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($event);
        $repo->method('save')->willReturn(true);

        $result = (new UpdateHandler($repo))(new Update('uuid-1', null));

        $this->assertNull($result->description);
    }

    public function test_throws_when_event_not_found(): void
    {
        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException('Not found'));

        $this->expectException(ModelNotFoundException::class);

        (new UpdateHandler($repo))(new Update('uuid-missing', 'New description'));
    }
}
