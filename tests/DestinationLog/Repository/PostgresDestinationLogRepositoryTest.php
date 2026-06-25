<?php

namespace Junction\Api\Test\DestinationLog\Repository;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Database\Contract\SelectInterface;
use Junction\Api\DestinationLog\Repository\PostgresDestinatonLogRepository;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;

final class PostgresDestinationLogRepositoryTest extends TestCase
{
    private function makeSelectQuery(): SelectInterface
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        return $query;
    }

    // getByEventLog

    public function test_get_by_event_log_returns_cursor_paginator(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresDestinatonLogRepository($db))->getByEventLog('elog-uuid', 25);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_get_by_event_log_filters_by_event_log_id(): void
    {
        $query = $this->makeSelectQuery();
        $query->expects($this->once())
            ->method('where')
            ->with('event_log_id', 'elog-uuid')
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgresDestinatonLogRepository($db))->getByEventLog('elog-uuid', 25);
    }

    // getByDestination

    public function test_get_by_destination_returns_cursor_paginator(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresDestinatonLogRepository($db))->getByDestination('dest-uuid', 25);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_get_by_destination_filters_by_destination_id(): void
    {
        $query = $this->makeSelectQuery();
        $query->expects($this->once())
            ->method('where')
            ->with('destination_id', 'dest-uuid')
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgresDestinatonLogRepository($db))->getByDestination('dest-uuid', 25);
    }

    // generateUuid

    public function test_generates_uuid_v7(): void
    {
        $repo   = new PostgresDestinatonLogRepository($this->createMock(DatabaseManagerInterface::class));
        $method = new \ReflectionMethod($repo, 'generateUuid');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $method->invoke($repo)
        );
    }
}
