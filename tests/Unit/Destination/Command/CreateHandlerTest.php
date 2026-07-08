<?php

namespace Junction\Api\Test\Unit\Destination\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\Create;
use Junction\Api\Destination\Command\CreateHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class CreateHandlerTest extends TestCase
{
    private DestinationType $type;

    /** @var Collection<Event> */
    private Collection $events;

    protected function setUp(): void
    {
        $this->type = new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [],
        ]);

        $this->events = new Collection([
            'event-uuid-1' => new Event(['id' => 'event-uuid-1', 'name' => 'order.placed']),
            'event-uuid-2' => new Event(['id' => 'event-uuid-2', 'name' => 'order.shipped']),
        ]);
    }

    private function makeCommand(array $overrides = []): Create
    {
        return new Create(
            name:        $overrides['name']   ?? 'My Webhook',
            description: array_key_exists('description', $overrides) ? $overrides['description'] : 'A test destination',
            config:      $overrides['config'] ?? ['url' => 'https://example.com/webhook'],
            status:      $overrides['status'] ?? 'active',
            events:      $overrides['events'] ?? [['name' => 'order.placed'], ['name' => 'order.shipped']],
            type:        $overrides['type']   ?? $this->type,
        );
    }

    private function makeHandler(?DestinationRepositoryInterface $repo = null, ?DispatcherInterface $dispatcher = null): CreateHandler
    {
        if (null === $repo) {
            $repo = $this->createMock(DestinationRepositoryInterface::class);
            $repo->method('save')->willReturnCallback(
                function (Destination $model) { $model->setPrimaryKeyValue('dest-uuid'); return true; }
            );
            $repo->method('attachEvents')->willReturn(1);
        }

        if (null === $dispatcher) {
            $dispatcher = $this->createMock(DispatcherInterface::class);
            $dispatcher->method('dispatch')->willReturn($this->events);
        }

        return new CreateHandler($repo, $dispatcher);
    }

    public function test_returns_destination_model(): void
    {
        $result = $this->makeHandler()($this->makeCommand());

        $this->assertInstanceOf(Destination::class, $result);
    }

    public function test_sets_name_from_command(): void
    {
        $result = $this->makeHandler()($this->makeCommand(['name' => 'My Webhook']));

        $this->assertSame('My Webhook', $result->name);
    }

    public function test_sets_description_from_command(): void
    {
        $result = $this->makeHandler()($this->makeCommand(['description' => 'A test destination']));

        $this->assertSame('A test destination', $result->description);
    }

    public function test_sets_null_description_from_command(): void
    {
        $result = $this->makeHandler()($this->makeCommand(['description' => null]));

        $this->assertNull($result->description);
    }

    public function test_sets_destination_type_id_from_command_type(): void
    {
        $result = $this->makeHandler()($this->makeCommand());

        $this->assertSame('type-uuid', $result->destinationTypeId);
    }

    public function test_sets_config_from_command(): void
    {
        $config = ['url' => 'https://example.com/webhook'];
        $result = $this->makeHandler()($this->makeCommand(['config' => $config]));

        $this->assertSame($config, $result->config);
    }

    public function test_sets_status_from_command(): void
    {
        $result = $this->makeHandler()($this->makeCommand(['status' => 'active']));

        $this->assertSame('active', $result->status);
    }

    public function test_saves_model_to_repository(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Destination $model) { $model->setPrimaryKeyValue('dest-uuid'); return true; });
        $repo->method('attachEvents')->willReturn(1);

        $this->makeHandler($repo)($this->makeCommand());
    }

    public function test_sets_destination_type_relation_on_returned_model(): void
    {
        $result = $this->makeHandler()($this->makeCommand());

        $this->assertSame($this->type, $result->getDestinationType());
    }

    public function test_dispatches_find_many_or_create_with_command_events(): void
    {
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($cmd) => $cmd->events === [['name' => 'order.placed']]))
            ->willReturn($this->events);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('save')->willReturnCallback(
            function (Destination $model) { $model->setPrimaryKeyValue('dest-uuid'); return true; }
        );
        $repo->method('attachEvents')->willReturn(1);

        $this->makeHandler($repo, $dispatcher)($this->makeCommand(['events' => [['name' => 'order.placed']]]));
    }

    public function test_attaches_event_ids_to_destination(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('save')->willReturnCallback(
            function (Destination $model) { $model->setPrimaryKeyValue('dest-uuid'); return true; }
        );
        $repo->expects($this->once())
            ->method('attachEvents')
            ->with('dest-uuid', ['event-uuid-1', 'event-uuid-2'])
            ->willReturn(2);

        $this->makeHandler($repo)($this->makeCommand());
    }

    public function test_sets_events_relation_on_returned_model(): void
    {
        $result = $this->makeHandler()($this->makeCommand());

        $this->assertSame($this->events, $result->getEvents());
    }
}
