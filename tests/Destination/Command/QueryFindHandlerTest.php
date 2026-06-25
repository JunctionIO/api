<?php

namespace Junction\Api\Test\Destination\Command;

use Junction\Api\Destination\Command\QueryFind;
use Junction\Api\Destination\Command\QueryFindHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class QueryFindHandlerTest extends TestCase
{
    private function makeDestination(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    private function makeType(): DestinationType
    {
        return new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [],
        ]);
    }

    private function makeHandler(
        ?DestinationRepositoryInterface $repo = null,
        ?DestinationTypeRepositoryInterface $types = null,
        ?EventRepositoryInterface $events = null,
    ): QueryFindHandler {
        if (null === $repo) {
            $repo = $this->createMock(DestinationRepositoryInterface::class);
            $repo->method('findOrFail')->willReturn($this->makeDestination());
            $repo->method('getEventIds')->willReturn([]);
        }

        if (null === $types) {
            $types = $this->createMock(DestinationTypeRepositoryInterface::class);
            $types->method('findById')->willReturn($this->makeType());
        }

        $events ??= $this->createMock(EventRepositoryInterface::class);

        return new QueryFindHandler($repo, $types, $events);
    }

    public function test_returns_destination_model(): void
    {
        $result = $this->makeHandler()(new QueryFind('dest-uuid'));

        $this->assertInstanceOf(Destination::class, $result);
    }

    public function test_calls_find_or_fail_with_command_id(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('dest-uuid')
            ->willReturn($this->makeDestination());
        $repo->method('getEventIds')->willReturn([]);

        $this->makeHandler($repo)(new QueryFind('dest-uuid'));
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);

        $this->makeHandler($repo)(new QueryFind('dest-uuid'));
    }

    public function test_fetches_destination_type_with_id_and_name_columns(): void
    {
        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('findById')
            ->with('type-uuid', ['id', 'name'])
            ->willReturn($this->makeType());

        $this->makeHandler(types: $types)(new QueryFind('dest-uuid'));
    }

    public function test_sets_destination_type_on_model(): void
    {
        $type = $this->makeType();

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('findById')->willReturn($type);

        $result = $this->makeHandler(types: $types)(new QueryFind('dest-uuid'));

        $this->assertSame($type, $result->getDestinationType());
    }

    public function test_loads_events_by_pivot_ids(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeDestination());
        $repo->expects($this->once())
            ->method('getEventIds')
            ->with('dest-uuid')
            ->willReturn(['event-uuid']);

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->once())
            ->method('getByIds')
            ->with(['event-uuid'], ['id', 'name'])
            ->willReturn(new Collection([]));

        $this->makeHandler($repo, events: $events)(new QueryFind('dest-uuid'));
    }

    public function test_sets_events_on_model(): void
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'order.placed']);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeDestination());
        $repo->method('getEventIds')->willReturn(['event-uuid']);

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->method('getByIds')->willReturn(new Collection(['event-uuid' => $event]));

        $result = $this->makeHandler($repo, events: $events)(new QueryFind('dest-uuid'));

        $this->assertSame($event, $result->getEvents()->get('event-uuid'));
    }

    public function test_sets_empty_events_collection_when_no_events(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeDestination());
        $repo->method('getEventIds')->willReturn([]);

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->never())->method('getByIds');

        $result = $this->makeHandler($repo, events: $events)(new QueryFind('dest-uuid'));

        $this->assertNotNull($result->getEvents());
        $this->assertTrue($result->getEvents()->isEmpty());
    }

    public function test_does_not_load_events_when_with_events_is_false(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeDestination());
        $repo->expects($this->never())->method('getEventIds');

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->expects($this->never())->method('getByIds');

        $this->makeHandler($repo, events: $events)(new QueryFind('dest-uuid', withEvents: false));
    }

    public function test_returns_null_events_when_with_events_is_false(): void
    {
        $result = $this->makeHandler()(new QueryFind('dest-uuid', withEvents: false));

        $this->assertNull($result->getEvents());
    }
}
