<?php

namespace Junction\Api\Test\Unit\DestinationLog\Command;

use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationLog\Command\QueryAllForDestination;
use Junction\Api\DestinationLog\Command\QueryAllForDestinationHandler;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;

final class QueryAllForDestinationHandlerTest extends TestCase
{
    private function makeDestination(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-25 10:00:00',
            'updated_at'          => '2026-06-25 10:00:00',
        ]);
    }

    private function makeEventLog(string $id): EventLog
    {
        return new EventLog([
            'id'          => $id,
            'trace_id'    => 'trace-uuid',
            'event_id'    => 'event-uuid',
            'payload'     => [],
            'auth_id'     => 'token',
            'received_at' => '2026-06-25 10:00:00',
            'created_at'  => '2026-06-25 10:00:00',
        ]);
    }

    private function makeDestinationLog(string $id, string $eventLogId): DestinationLog
    {
        return new DestinationLog([
            'id'             => $id,
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => $eventLogId,
            'destination_id' => 'dest-uuid',
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
    ): QueryAllForDestinationHandler {
        if (null === $repo) {
            $repo = $this->createMock(DestinationLogRepositoryInterface::class);
            $repo->method('getByDestination')->willReturn($this->makePaginator());
        }

        if (null === $eventLogs) {
            $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
            $eventLogs->method('getByIds')->willReturn(new Collection([]));
        }

        if (null === $destinations) {
            $destinations = $this->createMock(DestinationRepositoryInterface::class);
            $destinations->method('findOrFail')->willReturn($this->makeDestination());
        }

        return new QueryAllForDestinationHandler($repo, $eventLogs, $destinations);
    }

    public function test_returns_cursor_paginator(): void
    {
        $result = $this->makeHandler()(new QueryAllForDestination('dest-uuid', 25));

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_calls_find_or_fail_with_destination_id(): void
    {
        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->expects($this->once())
            ->method('findOrFail')
            ->with('dest-uuid')
            ->willReturn($this->makeDestination());

        $this->makeHandler(destinations: $destinations)(new QueryAllForDestination('dest-uuid', 25));
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);

        $this->makeHandler(destinations: $destinations)(new QueryAllForDestination('dest-uuid', 25));
    }

    public function test_fetches_event_logs_with_partial_columns(): void
    {
        $log  = $this->makeDestinationLog('dlog-1', 'elog-uuid');
        $elog = $this->makeEventLog('elog-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByDestination')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->expects($this->once())
            ->method('getByIds')
            ->with(['elog-uuid'], ['id', 'payload', 'source_ip', 'received_at'])
            ->willReturn(new Collection(['elog-uuid' => $elog]));

        $this->makeHandler($repo, $eventLogs)(new QueryAllForDestination('dest-uuid', 25));
    }

    public function test_deduplicates_event_log_ids(): void
    {
        $log1 = $this->makeDestinationLog('dlog-1', 'elog-uuid');
        $log2 = $this->makeDestinationLog('dlog-2', 'elog-uuid');
        $elog = $this->makeEventLog('elog-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByDestination')->willReturn($this->makePaginator([
            'dlog-1' => $log1,
            'dlog-2' => $log2,
        ]));

        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->expects($this->once())
            ->method('getByIds')
            ->with(['elog-uuid'], $this->anything())
            ->willReturn(new Collection(['elog-uuid' => $elog]));

        $this->makeHandler($repo, $eventLogs)(new QueryAllForDestination('dest-uuid', 25));
    }

    public function test_sets_event_log_on_each_model(): void
    {
        $log  = $this->makeDestinationLog('dlog-1', 'elog-uuid');
        $elog = $this->makeEventLog('elog-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByDestination')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->method('getByIds')->willReturn(new Collection(['elog-uuid' => $elog]));

        $result = $this->makeHandler($repo, $eventLogs)(new QueryAllForDestination('dest-uuid', 25));

        $this->assertSame($elog, $result->collection()->get('dlog-1')->getEventLog());
    }

    public function test_sets_destination_on_each_model(): void
    {
        $destination = $this->makeDestination();
        $log         = $this->makeDestinationLog('dlog-1', 'elog-uuid');
        $elog        = $this->makeEventLog('elog-uuid');

        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('getByDestination')->willReturn($this->makePaginator(['dlog-1' => $log]));

        $eventLogs = $this->createMock(EventLogRepositoryInterface::class);
        $eventLogs->method('getByIds')->willReturn(new Collection(['elog-uuid' => $elog]));

        $destinations = $this->createMock(DestinationRepositoryInterface::class);
        $destinations->method('findOrFail')->willReturn($destination);

        $result = $this->makeHandler($repo, $eventLogs, $destinations)(new QueryAllForDestination('dest-uuid', 25));

        $this->assertSame($destination, $result->collection()->get('dlog-1')->getDestination());
    }
}
