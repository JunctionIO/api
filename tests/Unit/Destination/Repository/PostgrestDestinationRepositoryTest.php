<?php

namespace Junction\Api\Test\Unit\Destination\Repository;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\SelectInterface;
use Junction\Api\Destination\Repository\PostgrestDestinationRepository;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;

final class PostgrestDestinationRepositoryTest extends TestCase
{
    private function makeSelectQuery(): SelectInterface
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);
        $query->method('whereIn')->willReturn($query);
        $query->method('orderBy')->willReturn($query);
        $query->method('limit')->willReturn($query);

        return $query;
    }

    private function makeDeleteQuery(): DeleteInterface
    {
        $query = $this->createMock(DeleteInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);
        $query->method('whereIn')->willReturn($query);

        return $query;
    }

    private function makeInsertQuery(): InsertInterface
    {
        $query = $this->createMock(InsertInterface::class);
        $query->method('into')->willReturn($query);
        $query->method('addRow')->willReturn($query);

        return $query;
    }

    // all

    public function test_all_returns_cursor_paginator(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgrestDestinationRepository($db))->all(25);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    // getByIds

    public function test_get_by_ids_returns_collection(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgrestDestinationRepository($db))->getByIds(['dest-1', 'dest-2']);

        $this->assertInstanceOf(\Meritum\Database\Support\Collection::class, $result);
    }

    public function test_get_by_ids_queries_by_id_column(): void
    {
        $query = $this->makeSelectQuery();
        $query->expects($this->once())
            ->method('whereIn')
            ->with('id', ['dest-1', 'dest-2'])
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgrestDestinationRepository($db))->getByIds(['dest-1', 'dest-2']);
    }

    // getEventIds

    public function test_get_event_ids_returns_array_of_ids(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([
            ['event_id' => 'event-1'],
            ['event_id' => 'event-2'],
        ]);

        $result = (new PostgrestDestinationRepository($db))->getEventIds('dest-uuid');

        $this->assertSame(['event-1', 'event-2'], $result);
    }

    public function test_get_event_ids_filters_by_destination_id(): void
    {
        $query = $this->makeSelectQuery();
        $query->expects($this->once())
            ->method('where')
            ->with('destination_id', 'dest-uuid')
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgrestDestinationRepository($db))->getEventIds('dest-uuid');
    }

    public function test_get_event_ids_returns_empty_array_when_no_results(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgrestDestinationRepository($db))->getEventIds('dest-uuid');

        $this->assertSame([], $result);
    }

    // getEventIdsForMany

    public function test_get_event_ids_for_many_returns_pivot_rows(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($this->makeSelectQuery());
        $db->method('fetchAll')->willReturn([
            ['event_id' => 'event-1', 'destination_id' => 'dest-1'],
            ['event_id' => 'event-2', 'destination_id' => 'dest-2'],
        ]);

        $result = (new PostgrestDestinationRepository($db))->getEventIdsForMany(['dest-1', 'dest-2']);

        $this->assertCount(2, $result);
        $this->assertSame('event-1', $result[0]['event_id']);
        $this->assertSame('dest-1', $result[0]['destination_id']);
    }

    public function test_get_event_ids_for_many_filters_by_destination_ids(): void
    {
        $query = $this->makeSelectQuery();
        $query->expects($this->once())
            ->method('whereIn')
            ->with('destination_id', ['dest-1', 'dest-2'])
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgrestDestinationRepository($db))->getEventIdsForMany(['dest-1', 'dest-2']);
    }

    // attachEvents

    public function test_attach_events_inserts_a_row_per_event(): void
    {
        $insert = $this->makeInsertQuery();
        $insert->expects($this->exactly(2))->method('addRow')->willReturn($insert);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('insert')->willReturn($insert);
        $db->method('fetchAffected')->willReturn(2);

        (new PostgrestDestinationRepository($db))->attachEvents('dest-uuid', ['event-1', 'event-2']);
    }

    public function test_attach_events_returns_affected_count(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('insert')->willReturn($this->makeInsertQuery());
        $db->method('fetchAffected')->willReturn(3);

        $result = (new PostgrestDestinationRepository($db))->attachEvents('dest-uuid', ['e1', 'e2', 'e3']);

        $this->assertSame(3, $result);
    }

    public function test_attach_events_with_replace_clears_existing_events_first(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('delete')->willReturn($this->makeDeleteQuery());
        $db->method('insert')->willReturn($this->makeInsertQuery());
        $db->expects($this->exactly(2))->method('fetchAffected')->willReturn(1);

        (new PostgrestDestinationRepository($db))->attachEvents('dest-uuid', ['event-1'], true);
    }

    public function test_attach_events_without_replace_does_not_delete(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->expects($this->never())->method('delete');
        $db->method('insert')->willReturn($this->makeInsertQuery());
        $db->method('fetchAffected')->willReturn(1);

        (new PostgrestDestinationRepository($db))->attachEvents('dest-uuid', ['event-1'], false);
    }

    // clearEvents

    public function test_clear_events_deletes_from_destination_events_table(): void
    {
        $delete = $this->makeDeleteQuery();
        $delete->expects($this->once())
            ->method('from')
            ->with('destination_events')
            ->willReturn($delete);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('delete')->willReturn($delete);
        $db->method('fetchAffected')->willReturn(0);

        (new PostgrestDestinationRepository($db))->clearEvents('dest-uuid');
    }

    public function test_clear_events_filters_by_destination_id(): void
    {
        $delete = $this->makeDeleteQuery();
        $delete->expects($this->once())
            ->method('where')
            ->with('destination_id', 'dest-uuid')
            ->willReturn($delete);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('delete')->willReturn($delete);
        $db->method('fetchAffected')->willReturn(0);

        (new PostgrestDestinationRepository($db))->clearEvents('dest-uuid');
    }

    public function test_clear_events_returns_affected_count(): void
    {
        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('delete')->willReturn($this->makeDeleteQuery());
        $db->method('fetchAffected')->willReturn(5);

        $result = (new PostgrestDestinationRepository($db))->clearEvents('dest-uuid');

        $this->assertSame(5, $result);
    }
}
