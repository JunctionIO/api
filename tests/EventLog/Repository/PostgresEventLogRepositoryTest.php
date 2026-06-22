<?php

namespace Junction\Api\Test\EventLog\Repository;

use PHPUnit\Framework\TestCase;
use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\EventLog\Repository\PostgresEventLogRepository;

final class PostgresEventLogRepositoryTest extends TestCase
{
    public function test_all_returns_cursor_paginator(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresEventLogRepository($db))->all(25);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_all_for_events_returns_cursor_paginator(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('whereIn')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresEventLogRepository($db))->allForEvents(['event-uuid-1'], 25);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function test_all_for_events_queries_by_event_id_column(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        $query->expects($this->once())
            ->method('whereIn')
            ->with('event_id', ['uuid-1', 'uuid-2'])
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgresEventLogRepository($db))->allForEvents(['uuid-1', 'uuid-2'], 25);
    }

    public function test_generates_uuid_v7(): void
    {
        $db   = $this->createMock(DatabaseManagerInterface::class);
        $repo = new PostgresEventLogRepository($db);

        $method = new \ReflectionMethod($repo, 'generateUuid');
        $uuid   = $method->invoke($repo);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
    }
}
