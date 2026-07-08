<?php

namespace Junction\Api\Test\Unit\Destination\Command;

use Junction\Api\Destination\Command\QueryAll;
use Junction\Api\Destination\Command\QueryAllHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;

final class QueryAllHandlerTest extends TestCase
{
    private function makeDestination(string $id, string $typeId = 'type-uuid'): Destination
    {
        return new Destination([
            'id'                  => $id,
            'name'                => 'My Webhook',
            'destination_type_id' => $typeId,
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    private function makeType(string $id = 'type-uuid'): DestinationType
    {
        return new DestinationType(['id' => $id, 'name' => 'http', 'queue' => 'junction.destinations.http', 'config_schema' => []]);
    }

    private function makeEvent(string $id, string $name): Event
    {
        return new Event(['id' => $id, 'name' => $name]);
    }

    private function makeHandler(
        ?DestinationRepositoryInterface $repo = null,
        ?DestinationTypeRepositoryInterface $types = null,
        ?EventRepositoryInterface $events = null,
    ): QueryAllHandler {
        $repo   ??= $this->createMock(DestinationRepositoryInterface::class);
        $types  ??= $this->createMock(DestinationTypeRepositoryInterface::class);
        $events ??= $this->createMock(EventRepositoryInterface::class);

        return new QueryAllHandler($repo, $types, $events);
    }

    public function test_returns_cursor_paginator(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn($paginator);

        $result = $this->makeHandler($repo)(new QueryAll(25));

        $this->assertSame($paginator, $result);
    }

    public function test_passes_limit_and_cursor_to_repository(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('all')
            ->with(50, 'tok123')
            ->willReturn(new CursorPaginator(new Collection([]), null, null, 50));

        $this->makeHandler($repo)(new QueryAll(50, 'tok123'));
    }

    public function test_does_not_fetch_types_or_events_when_collection_is_empty(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection([]), null, null, 25));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->never())->method('getByIds');

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->never())->method('getByIds');

        $this->makeHandler($repo, $types, $events)(new QueryAll(25));
    }

    public function test_fetches_destination_types_by_collected_ids(): void
    {
        $dest = $this->makeDestination('dest-1', 'type-uuid');
        $type = $this->makeType('type-uuid');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('getByIds')
            ->with(['type-uuid'], ['id', 'name'])
            ->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryAll(25));
    }

    public function test_deduplicates_type_ids_before_fetching(): void
    {
        $dest1 = $this->makeDestination('dest-1', 'type-uuid');
        $dest2 = $this->makeDestination('dest-2', 'type-uuid');
        $type  = $this->makeType('type-uuid');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('getByIds')
            ->with(['type-uuid'], ['id', 'name'])
            ->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryAll(25));
    }

    public function test_sets_destination_type_on_each_destination(): void
    {
        $dest = $this->makeDestination('dest-1', 'type-uuid');
        $type = $this->makeType('type-uuid');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryAll(25));

        $this->assertSame($type, $dest->getDestinationType());
    }

    public function test_fetches_event_ids_for_all_destination_ids(): void
    {
        $dest = $this->makeDestination('dest-1');
        $type = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest]), null, null, 25));
        $repo->expects($this->once())
            ->method('getEventIdsForMany')
            ->with(['dest-1'])
            ->willReturn([]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryAll(25));
    }

    public function test_does_not_fetch_events_when_no_pivot_results(): void
    {
        $dest = $this->makeDestination('dest-1');
        $type = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->never())->method('getByIds');

        $this->makeHandler($repo, $types, $events)(new QueryAll(25));
    }

    public function test_deduplicates_event_ids_before_fetching(): void
    {
        $dest1 = $this->makeDestination('dest-1');
        $dest2 = $this->makeDestination('dest-2');
        $type  = $this->makeType();
        $event = $this->makeEvent('event-1', 'order.placed');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([
            ['destination_id' => 'dest-1', 'event_id' => 'event-1'],
            ['destination_id' => 'dest-2', 'event_id' => 'event-1'],
        ]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->once())
            ->method('getByIds')
            ->with(['event-1'], ['id', 'name'])
            ->willReturn(new Collection(['event-1' => $event]));

        $this->makeHandler($repo, $types, $events)(new QueryAll(25));
    }

    public function test_sets_events_on_each_destination(): void
    {
        $dest  = $this->makeDestination('dest-1');
        $type  = $this->makeType();
        $event = $this->makeEvent('event-1', 'order.placed');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([
            ['destination_id' => 'dest-1', 'event_id' => 'event-1'],
        ]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->method('getByIds')->willReturn(new Collection(['event-1' => $event]));

        $this->makeHandler($repo, $types, $events)(new QueryAll(25));

        $this->assertSame($event, $dest->getEvents()->get('event-1'));
    }

    public function test_sets_empty_events_collection_on_destinations_with_no_subscriptions(): void
    {
        $dest1 = $this->makeDestination('dest-1');
        $dest2 = $this->makeDestination('dest-2');
        $type  = $this->makeType();
        $event = $this->makeEvent('event-1', 'order.placed');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('all')->willReturn(new CursorPaginator(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]), null, null, 25));
        $repo->method('getEventIdsForMany')->willReturn([
            ['destination_id' => 'dest-1', 'event_id' => 'event-1'],
        ]);

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->method('getByIds')->willReturn(new Collection(['event-1' => $event]));

        $this->makeHandler($repo, $types, $events)(new QueryAll(25));

        $this->assertTrue($dest2->getEvents()->isEmpty());
    }
}
