<?php

namespace Junction\Api\Test\Destination\Command;

use Junction\Api\Destination\Command\QueryActiveByEvent;
use Junction\Api\Destination\Command\QueryActiveByEventHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class QueryActiveByEventHandlerTest extends TestCase
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

    private function makeHandler(
        ?DestinationRepositoryInterface $repo = null,
        ?DestinationTypeRepositoryInterface $types = null,
    ): QueryActiveByEventHandler {
        $repo  ??= $this->createMock(DestinationRepositoryInterface::class);
        $types ??= $this->createMock(DestinationTypeRepositoryInterface::class);

        return new QueryActiveByEventHandler($repo, $types);
    }

    public function test_returns_collection_from_repository(): void
    {
        $dest = $this->makeDestination('dest-1');
        $type = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $result = $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));

        $this->assertCount(1, $result);
    }

    public function test_passes_event_id_to_repository(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getActiveByEvent')
            ->with('event-uuid')
            ->willReturn(new Collection([]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection([]));

        $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));
    }

    public function test_returns_empty_collection_when_no_active_destinations(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection([]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection([]));

        $result = $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));

        $this->assertCount(0, $result);
    }

    public function test_fetches_destination_types_with_required_columns(): void
    {
        $dest = $this->makeDestination('dest-1', 'type-uuid');
        $type = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('getByIds')
            ->with(['type-uuid'], ['id', 'name', 'queue'])
            ->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));
    }

    public function test_deduplicates_type_ids_before_fetching(): void
    {
        $dest1 = $this->makeDestination('dest-1', 'type-uuid');
        $dest2 = $this->makeDestination('dest-2', 'type-uuid');
        $type  = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('getByIds')
            ->with(['type-uuid'], ['id', 'name', 'queue'])
            ->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));
    }

    public function test_collects_multiple_distinct_type_ids(): void
    {
        $dest1 = $this->makeDestination('dest-1', 'type-a');
        $dest2 = $this->makeDestination('dest-2', 'type-b');
        $types = new Collection([
            'type-a' => $this->makeType('type-a'),
            'type-b' => $this->makeType('type-b'),
        ]);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]));

        $typeRepo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $typeRepo->expects($this->once())
            ->method('getByIds')
            ->with($this->callback(fn(array $ids) => count($ids) === 2))
            ->willReturn($types);

        $this->makeHandler($repo, $typeRepo)(new QueryActiveByEvent('event-uuid'));
    }

    public function test_sets_destination_type_on_each_destination(): void
    {
        $dest = $this->makeDestination('dest-1', 'type-uuid');
        $type = $this->makeType();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-uuid' => $type]));

        $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));

        $this->assertSame($type, $dest->getDestinationType());
    }

    public function test_sets_correct_type_on_each_destination_when_multiple_types(): void
    {
        $typeA = $this->makeType('type-a');
        $typeB = $this->makeType('type-b');
        $dest1 = $this->makeDestination('dest-1', 'type-a');
        $dest2 = $this->makeDestination('dest-2', 'type-b');

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getActiveByEvent')->willReturn(new Collection(['dest-1' => $dest1, 'dest-2' => $dest2]));

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('getByIds')->willReturn(new Collection(['type-a' => $typeA, 'type-b' => $typeB]));

        $this->makeHandler($repo, $types)(new QueryActiveByEvent('event-uuid'));

        $this->assertSame($typeA, $dest1->getDestinationType());
        $this->assertSame($typeB, $dest2->getDestinationType());
    }
}
