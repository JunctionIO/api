<?php

namespace Junction\Api\Test\Unit\DestinationType\Repository;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Database\Contract\SelectInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\Repository\PostgresDestinationTypeRepository;
use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class PostgresDestinationTypeRepositoryTest extends TestCase
{
    public function test_all_returns_collection(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('orderBy')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresDestinationTypeRepository($db))->all();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_all_orders_by_id_ascending(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);

        $query->expects($this->once())
            ->method('orderBy')
            ->with('id', 'ASC')
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgresDestinationTypeRepository($db))->all();
    }

    public function test_get_by_ids_returns_collection(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('whereIn')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        $result = (new PostgresDestinationTypeRepository($db))->getByIds(['uuid-1']);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_get_by_ids_queries_by_id_column(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);

        $query->expects($this->once())
            ->method('whereIn')
            ->with('id', ['uuid-1', 'uuid-2'])
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchAll')->willReturn([]);

        (new PostgresDestinationTypeRepository($db))->getByIds(['uuid-1', 'uuid-2']);
    }

    public function test_find_by_id_returns_destination_type_when_found(): void
    {
        $row   = ['id' => 'uuid-1', 'name' => 'http', 'queue' => 'junction.destinations.http', 'description' => null, 'config_schema' => '[]', 'created_at' => null, 'updated_at' => null];
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchOne')->willReturn($row);

        $result = (new PostgresDestinationTypeRepository($db))->findById('uuid-1');

        $this->assertInstanceOf(DestinationType::class, $result);
        $this->assertSame('uuid-1', $result->id);
    }

    public function test_find_by_id_throws_when_not_found(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchOne')->willReturn(null);

        $this->expectException(ModelNotFoundException::class);

        (new PostgresDestinationTypeRepository($db))->findById('uuid-missing');
    }

    public function test_find_by_id_queries_by_id_column(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);

        $query->expects($this->once())
            ->method('where')
            ->with('id', 'uuid-1')
            ->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('select')->willReturn($query);
        $db->method('fetchOne')->willReturn(null);

        try {
            (new PostgresDestinationTypeRepository($db))->findById('uuid-1');
        } catch (ModelNotFoundException) {
        }
    }

    public function test_find_by_id_passes_columns_to_query(): void
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('from')->willReturn($query);
        $query->method('where')->willReturn($query);

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->expects($this->once())
            ->method('select')
            ->with(['id', 'name', 'queue'])
            ->willReturn($query);
        $db->method('fetchOne')->willReturn(null);

        try {
            (new PostgresDestinationTypeRepository($db))->findById('uuid-1', ['id', 'name', 'queue']);
        } catch (ModelNotFoundException) {
        }
    }
}
