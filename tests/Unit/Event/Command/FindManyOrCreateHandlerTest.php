<?php

namespace Junction\Api\Test\Unit\Event\Command;

use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Event\Command\FindManyOrCreate;
use Junction\Api\Event\Command\FindManyOrCreateHandler;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class FindManyOrCreateHandlerTest extends TestCase
{
    public function test_returns_existing_events_when_all_found(): void
    {
        $existing = new Collection([
            'uuid-1' => new Event(['id' => 'uuid-1', 'name' => 'order.placed']),
            'uuid-2' => new Event(['id' => 'uuid-2', 'name' => 'order.shipped']),
        ]);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn($existing);
        $repo->expects($this->never())->method('insertMany');

        $command = new FindManyOrCreate([
            ['name' => 'order.placed'],
            ['name' => 'order.shipped'],
        ]);

        $result = (new FindManyOrCreateHandler($repo))($command);

        $this->assertCount(2, $result);
    }

    public function test_creates_events_not_found_in_repository(): void
    {
        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn(new Collection([]));
        $repo->expects($this->once())
            ->method('insertMany')
            ->willReturnCallback(function (array $models) {
                $collect = [];
                foreach ($models as $model) {
                    $collect['new-uuid'] = $model;
                }
                return new Collection($collect);
            });

        $command = new FindManyOrCreate([['name' => 'order.placed']]);

        $result = (new FindManyOrCreateHandler($repo))($command);

        $this->assertCount(1, $result);
    }

    public function test_creates_only_missing_events(): void
    {
        $existing = new Collection([
            'uuid-1' => new Event(['id' => 'uuid-1', 'name' => 'order.placed']),
        ]);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn($existing);
        $repo->expects($this->once())
            ->method('insertMany')
            ->with($this->callback(function (array $models) {
                return 1 === count($models) && 'order.shipped' === $models[0]->name;
            }))
            ->willReturnCallback(function (array $models) {
                $collect = [];
                foreach ($models as $model) {
                    $collect['new-uuid'] = $model;
                }
                return new Collection($collect);
            });

        $command = new FindManyOrCreate([
            ['name' => 'order.placed'],
            ['name' => 'order.shipped'],
        ]);

        $result = (new FindManyOrCreateHandler($repo))($command);

        $this->assertCount(2, $result);
    }

    public function test_returns_collection(): void
    {
        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn(new Collection([]));
        $repo->method('insertMany')->willReturn(new Collection([]));

        $command = new FindManyOrCreate([['name' => 'order.placed']]);

        $result = (new FindManyOrCreateHandler($repo))($command);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_does_not_call_insert_many_when_all_events_exist(): void
    {
        $existing = new Collection([
            'uuid-1' => new Event(['id' => 'uuid-1', 'name' => 'order.placed']),
        ]);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn($existing);
        $repo->expects($this->never())->method('insertMany');

        $command = new FindManyOrCreate([['name' => 'order.placed']]);

        (new FindManyOrCreateHandler($repo))($command);
    }
}
