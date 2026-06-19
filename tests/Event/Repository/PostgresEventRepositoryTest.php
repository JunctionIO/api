<?php

namespace Junction\Api\Test\Event\Repository;

use PHPUnit\Framework\TestCase;
use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Event\Repository\PostgresEventRepository;

final class PostgresEventRepositoryTest extends TestCase
{
    public function test_get_by_ids_returns_collection(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('whereIn')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([
            ['id' => 'uuid-1', 'name' => 'event.one', 'description' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => 'uuid-2', 'name' => 'event.two', 'description' => null, 'created_at' => null, 'updated_at' => null],
        ]);

        $result = (new PostgresEventRepository($db))->getByIds(['uuid-1', 'uuid-2']);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_by_ids_queries_by_id_column(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('whereIn')->willReturn($query);

        $query->expects($this->once())
            ->method('whereIn')
            ->with('id', ['uuid-1'])
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([
            ['id' => 'uuid-1', 'name' => 'event.one', 'description' => null, 'created_at' => null, 'updated_at' => null],
        ]);

        (new PostgresEventRepository($db))->getByIds(['uuid-1']);
    }

    public function test_all_returns_cursor_paginator(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([
            ['id' => 'uuid-1', 'name' => 'event.one', 'description' => null, 'created_at' => null, 'updated_at' => null],
        ]);

        $result = (new PostgresEventRepository($db))->all(10);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

}
