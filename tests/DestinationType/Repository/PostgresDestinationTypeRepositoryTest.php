<?php

namespace Junction\Api\Test\DestinationType\Repository;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Database\Contract\SelectInterface;
use Junction\Api\DestinationType\Repository\PostgresDestinationTypeRepository;
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
}
