<?php

namespace Junction\Api\Test\DestinationLog\Command;

use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationLog\Command\QueryAllForEventLog;
use Junction\Api\DestinationLog\Command\QueryAllForEventLogHandler;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;

final class QueryAllForEventLogHandlerTest extends TestCase
{
    private function makeEventLog(): EventLog
    {
        return new EventLog([
            'id'          => 'elog-uuid',
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => [],
            'auth_id'     => 'token',
            'received_at' => '2026-06-25 10:00:00',
            'created_at'  => '2026-06-25 10:00:00',
        ]);
    }

    private function makeDestination(string $id): Destination
    {
        return new Destination([
            'id'                  => $id,
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-25 10:00:00',
            'updated_at'          => '2026-06-25 10:00:00',
        ]);
    }

    private function makeDestinationLog(string $id, string $destinationId): DestinationLog
    {
        return new DestinationLog([
            'id'             => $id,
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'elog-uuid',
            'destination_id' => $destinationId,
            'status'         => 'pending',
            'created_at'     => '2026-06-25 10:00:00',
            'updated_at'     => '2026-06-25 10:00:00',
        ]);
    }

    private function makePaginator(array $models = []): CursorPaginator
    {
        return new CursorPaginator(new Collection($models), null, null, 25);
    }

    private function makeHandler(
        ?DestinationLogRepositoryInterface $repo = null,
        ?EventLogRepositoryInterface $eventLogs = null,
        ?DestinationRepositoryInterface $destinations = null,
    ): QueryAllForEventLogHandler {
        if (null === $repo) {
            $repo = $this->createMock(DestinationLogRepositoryInterface::class);
            $repo->method('getByEventLog')->willReturn($this->makePaginator());
        }

        if (null === $eventLogs) {
            $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
            $eventLogs->method('findOrFail')->willReturn($this->makeEventLog());
        }

        if (null === $destinations) {
            $destinations = $this->createMock(DestinationRepositoryInterface::class);
            $destinations->method('getByIds')->willReturn(new Collection([]));
        }

        return new QueryAllForEventLogHandler($repo, $eventLogs, $destinations);
    }

    public function test_returns_cursor_paginator(): void
    {
        $result = $this->makeHandler()(new QueryAllForEventLog('elog-uuid', 25));

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_calls_find_or_fail_with_event_log_id(): void
    {
        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->expects($this->once())
            ->method('findOrFail')
            ->with('elog-uuid')
            ->willReturn($this->makeEventLog());

        $this->makeHandler(eventLogs: $eventLogs)(new QueryAllForEventLog('elog-uuid', 25));
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);

        $this->makeHandler(eventLogs: $eventLogs)(new QueryAllForEventLog('elog-uuid', 25));
    }

    public function test_fetches_destinations_with_partial_columns(): void
    {
        $log  = $this->makeDestinationLog('dlog-1', 'dest-uuid');
        $dest = $this->makeDestination('dest-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByEventLog')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->expects($this->once())
            ->method('getByIds')
            ->with(['dest-uuid'], ['id', 'name', 'description'])
            ->willReturn(new Collection(['dest-uuid' => $dest]));

        $this->makeHandler($repo, destinations: $destinations)(new QueryAllForEventLog('elog-uuid', 25));
    }

    public function test_deduplicates_destination_ids(): void
    {
        $log1 = $this->makeDestinationLog('dlog-1', 'dest-uuid');
        $log2 = $this->makeDestinationLog('dlog-2', 'dest-uuid');
        $dest = $this->makeDestination('dest-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByEventLog')->willReturn($this->makePaginator([
            'dlog-1' => $log1,
            'dlog-2' => $log2,
        ]));

        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->expects($this->once())
            ->method('getByIds')
            ->with(['dest-uuid'], $this->anything())
            ->willReturn(new Collection(['dest-uuid' => $dest]));

        $this->makeHandler($repo, destinations: $destinations)(new QueryAllForEventLog('elog-uuid', 25));
    }

    public function test_sets_destination_on_each_model(): void
    {
        $log  = $this->makeDestinationLog('dlog-1', 'dest-uuid');
        $dest = $this->makeDestination('dest-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByEventLog')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->method('getByIds')->willReturn(new Collection(['dest-uuid' => $dest]));

        $result = $this->makeHandler($repo, destinations: $destinations)(new QueryAllForEventLog('elog-uuid', 25));

        $this->assertSame($dest, $result->collection()->get('dlog-1')->getDestination());
    }

    public function test_sets_event_log_on_each_model(): void
    {
        $eventLog = $this->makeEventLog();
        $log      = $this->makeDestinationLog('dlog-1', 'dest-uuid');
        $dest     = $this->makeDestination('dest-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByEventLog')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->method('findOrFail')->willReturn($eventLog);

        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->method('getByIds')->willReturn(new Collection(['dest-uuid' => $dest]));

        $result = $this->makeHandler($repo, $eventLogs, $destinations)(new QueryAllForEventLog('elog-uuid', 25));

        $this->assertSame($eventLog, $result->collection()->get('dlog-1')->getEventLog());
    }
}
