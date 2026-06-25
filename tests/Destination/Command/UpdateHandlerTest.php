<?php

namespace Junction\Api\Test\Destination\Command;

use Junction\Api\Destination\Command\Update;
use Junction\Api\Destination\Command\UpdateHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UpdateHandlerTest extends TestCase
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
    ): UpdateHandler {
        if (null === $repo) {
            $repo = $this->createMock(DestinationRepositoryInterface::class);
            $repo->method('getEventIds')->willReturn([]);
            $repo->method('save')->willReturn(true);
        }

        if (null === $types) {
            $types = $this->createMock(DestinationTypeRepositoryInterface::class);
            $types->method('findById')->willReturn($this->makeType());
        }

        if (null === $events) {
            $events = $this->createMock(EventRepositoryInterface::class);
        }

        return new UpdateHandler($repo, $types, $events);
    }

    public function test_returns_destination_model(): void
    {
        $model  = $this->makeDestination();
        $result = $this->makeHandler()(new Update($model, []));

        $this->assertSame($model, $result);
    }

    public function test_always_loads_destination_type(): void
    {
        $model = $this->makeDestination();

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->expects($this->once())
            ->method('findById')
            ->with('type-uuid', ['id', 'name'])
            ->willReturn($this->makeType());

        $this->makeHandler(types: $types)(new Update($model, []));
    }

    public function test_always_loads_events(): void
    {
        $model = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getEventIds')
            ->with('dest-uuid')
            ->willReturn([]);

        $this->makeHandler($repo)(new Update($model, []));
    }

    public function test_sets_destination_type_on_model(): void
    {
        $model = $this->makeDestination();
        $type  = $this->makeType();

        $types = $this->createMock(DestinationTypeRepositoryInterface::class);
        $types->method('findById')->willReturn($type);

        $result = $this->makeHandler(types: $types)(new Update($model, []));

        $this->assertSame($type, $result->getDestinationType());
    }

    public function test_sets_events_on_model(): void
    {
        $model = $this->makeDestination();
        $event = new Event(['id' => 'event-uuid', 'name' => 'order.placed']);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getEventIds')->willReturn(['event-uuid']);
        $repo->method('save')->willReturn(true);

        $events = $this->createMock(EventRepositoryInterface::class);
        $events->method('getByIds')->willReturn(new Collection(['event-uuid' => $event]));

        $result = $this->makeHandler($repo, events: $events)(new Update($model, []));

        $this->assertSame($event, $result->getEvents()->get('event-uuid'));
    }

    public function test_does_not_call_save_when_data_is_empty(): void
    {
        $model = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getEventIds')->willReturn([]);
        $repo->expects($this->never())->method('save');

        $this->makeHandler($repo)(new Update($model, []));
    }

    public function test_calls_save_when_data_is_present(): void
    {
        $model = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('getEventIds')->willReturn([]);
        $repo->expects($this->once())->method('save')->with($model)->willReturn(true);

        $this->makeHandler($repo)(new Update($model, ['name' => 'Updated']));
    }

    public function test_updates_only_fields_present_in_data(): void
    {
        $model = $this->makeDestination();

        $result = $this->makeHandler()(new Update($model, ['name' => 'Updated Webhook']));

        $this->assertSame('Updated Webhook', $result->name);
        $this->assertSame('active', $result->status);
    }

    public function test_updates_all_patchable_fields(): void
    {
        $model = $this->makeDestination();

        $result = $this->makeHandler()(new Update($model, [
            'name'        => 'New Name',
            'description' => 'New description',
            'config'      => ['url' => 'https://example.com'],
            'status'      => 'disabled',
        ]));

        $this->assertSame('New Name', $result->name);
        $this->assertSame('New description', $result->description);
        $this->assertSame(['url' => 'https://example.com'], $result->config);
        $this->assertSame('disabled', $result->status);
    }

    public function test_sets_description_to_null_when_explicitly_passed(): void
    {
        $model = $this->makeDestination();

        $result = $this->makeHandler()(new Update($model, ['description' => null]));

        $this->assertNull($result->description);
    }
}
