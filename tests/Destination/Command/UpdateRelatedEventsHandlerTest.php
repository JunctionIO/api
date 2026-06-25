<?php

namespace Junction\Api\Test\Destination\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\QueryFind;
use Junction\Api\Destination\Command\UpdateRelatedEvents;
use Junction\Api\Destination\Command\UpdateRelatedEventsHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\Event\Command\FindManyOrCreate;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UpdateRelatedEventsHandlerTest extends TestCase
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

    private function makeDispatcher(?Destination $model = null, ?Collection $events = null): DispatcherInterface
    {
        $model  = $model  ?? $this->makeDestination();
        $events = $events ?? new Collection([]);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnCallback(fn(object $cmd) => match (true) {
                $cmd instanceof QueryFind        => $model,
                $cmd instanceof FindManyOrCreate => $events,
            });

        return $dispatcher;
    }

    private function makeHandler(
        ?DispatcherInterface $dispatcher = null,
        ?DestinationRepositoryInterface $repo = null,
    ): UpdateRelatedEventsHandler {
        $dispatcher ??= $this->makeDispatcher();

        if (null === $repo) {
            $repo = $this->createMock(DestinationRepositoryInterface::class);
            $repo->method('attachEvents')->willReturn(0);
        }

        return new UpdateRelatedEventsHandler($dispatcher, $repo);
    }

    public function test_returns_destination_model(): void
    {
        $result = $this->makeHandler()(new UpdateRelatedEvents('dest-uuid', []));

        $this->assertInstanceOf(Destination::class, $result);
    }

    public function test_dispatches_query_find_with_id_and_without_events(): void
    {
        $capturedQueryFind = null;
        $model             = $this->makeDestination();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnCallback(function (object $cmd) use (&$capturedQueryFind, $model) {
                if ($cmd instanceof QueryFind) {
                    $capturedQueryFind = $cmd;

                    return $model;
                }

                return new Collection([]);
            });

        $this->makeHandler($dispatcher)(new UpdateRelatedEvents('dest-uuid', []));

        $this->assertNotNull($capturedQueryFind);
        $this->assertSame('dest-uuid', $capturedQueryFind->id);
        $this->assertFalse($capturedQueryFind->withEvents);
    }

    public function test_dispatches_find_many_or_create_with_event_data(): void
    {
        $eventData             = [['name' => 'order.placed'], ['name' => 'order.updated']];
        $capturedFindManyOrCreate = null;

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnCallback(function (object $cmd) use (&$capturedFindManyOrCreate) {
                if ($cmd instanceof FindManyOrCreate) {
                    $capturedFindManyOrCreate = $cmd;

                    return new Collection([]);
                }

                return $this->makeDestination();
            });

        $this->makeHandler($dispatcher)(new UpdateRelatedEvents('dest-uuid', $eventData));

        $this->assertNotNull($capturedFindManyOrCreate);
        $this->assertSame($eventData, $capturedFindManyOrCreate->events);
    }

    public function test_attaches_event_ids_with_replace_true(): void
    {
        $event  = new Event(['id' => 'event-uuid', 'name' => 'order.placed']);
        $events = new Collection(['event-uuid' => $event]);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('attachEvents')
            ->with('dest-uuid', ['event-uuid'], true)
            ->willReturn(1);

        $this->makeHandler($this->makeDispatcher(events: $events), $repo)(
            new UpdateRelatedEvents('dest-uuid', [['name' => 'order.placed']])
        );
    }

    public function test_attaches_empty_array_when_no_events_returned(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('attachEvents')
            ->with('dest-uuid', [], true)
            ->willReturn(0);

        $this->makeHandler(repo: $repo)(new UpdateRelatedEvents('dest-uuid', []));
    }

    public function test_sets_events_on_returned_model(): void
    {
        $event  = new Event(['id' => 'event-uuid', 'name' => 'order.placed']);
        $events = new Collection(['event-uuid' => $event]);

        $result = $this->makeHandler($this->makeDispatcher(events: $events))(
            new UpdateRelatedEvents('dest-uuid', [['name' => 'order.placed']])
        );

        $this->assertSame($event, $result->getEvents()->get('event-uuid'));
    }
}
