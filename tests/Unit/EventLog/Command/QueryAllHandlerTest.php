<?php

namespace Junction\Api\Test\Unit\EventLog\Command;

use PHPUnit\Framework\TestCase;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\Command\QueryAll;
use Junction\Api\EventLog\Command\QueryAllHandler;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;

final class QueryAllHandlerTest extends TestCase
{
    public function test_calls_all_when_no_filter(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->expects($this->once())->method('all')->with(25, null)->willReturn($paginator);
        $repo->expects($this->never())->method('allForEvents');

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25));
    }

    public function test_returns_empty_paginator_when_filter_yields_no_events(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->expects($this->never())->method('all');
        $repo->expects($this->never())->method('allForEvents');

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        $result = (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25, null, []));

        $this->assertInstanceOf(CursorPaginator::class, $result);
        $this->assertCount(0, $result->collection());
    }

    public function test_calls_all_for_events_when_filter_supplied(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('allForEvents')
            ->with(['event-uuid-1'], 25, null)
            ->willReturn($paginator);
        $repo->expects($this->never())->method('all');

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25, null, ['event-uuid-1']));
    }

    public function test_passes_cursor_to_repository(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->expects($this->once())->method('all')->with(25, 'tok123')->willReturn($paginator);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25, 'tok123'));
    }

    public function test_sets_related_event_on_each_log(): void
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $log   = new EventLog(['id' => 'log-uuid', 'event_id' => 'event-uuid']);

        $paginator = new CursorPaginator(new Collection(['log-uuid' => $log]), null, null, 25);
        $events    = new Collection(['event-uuid' => $event]);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('all')->willReturn($paginator);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);
        $eventRepo->expects($this->once())
            ->method('getByIds')
            ->with(['event-uuid'], ['id', 'name'])
            ->willReturn($events);

        (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25));

        $this->assertSame('test.event', $log->getEvent()->name);
    }

    public function test_does_not_fetch_events_when_collection_is_empty(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('all')->willReturn($paginator);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);
        $eventRepo->expects($this->never())->method('getByIds');

        (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25));
    }

    public function test_returns_cursor_paginator(): void
    {
        $paginator = new CursorPaginator(new Collection([]), 'next-tok', 'prev-tok', 25);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('all')->willReturn($paginator);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        $result = (new QueryAllHandler($repo, $eventRepo))(new QueryAll(25));

        $this->assertSame($paginator, $result);
    }
}
