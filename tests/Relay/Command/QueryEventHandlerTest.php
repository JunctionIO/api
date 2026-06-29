<?php

namespace Junction\Api\Test\Relay\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\QueryActiveByEvent;
use Junction\Api\Event\Command\FindOrCreate;
use Junction\Api\Event\Event;
use Junction\Api\Relay\Command\QueryEvent;
use Junction\Api\Relay\Command\QueryEventHandler;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class QueryEventHandlerTest extends TestCase
{
    private function makeEvent(string $id = 'event-uuid', string $name = 'order.placed'): Event
    {
        return new Event(['id' => $id, 'name' => $name]);
    }

    public function test_returns_event(): void
    {
        $event = $this->makeEvent();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnOnConsecutiveCalls($event, new Collection([]));

        $result = (new QueryEventHandler($dispatcher))(new QueryEvent('order.placed'));

        $this->assertSame($event, $result);
    }

    public function test_dispatches_find_or_create_with_event_name(): void
    {
        $event = $this->makeEvent();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (mixed $cmd) use ($event) {
                if ($cmd instanceof FindOrCreate) {
                    $this->assertSame('order.placed', $cmd->name);
                    return $event;
                }
                return new Collection([]);
            });

        (new QueryEventHandler($dispatcher))(new QueryEvent('order.placed'));
    }

    public function test_dispatches_query_active_by_event_with_event_id(): void
    {
        $event = $this->makeEvent('event-uuid');

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (mixed $cmd) use ($event) {
                if ($cmd instanceof FindOrCreate) {
                    return $event;
                }
                if ($cmd instanceof QueryActiveByEvent) {
                    $this->assertSame('event-uuid', $cmd->eventId);
                }
                return new Collection([]);
            });

        (new QueryEventHandler($dispatcher))(new QueryEvent('order.placed'));
    }

    public function test_sets_destinations_on_event(): void
    {
        $event        = $this->makeEvent();
        $destinations = new Collection([]);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnOnConsecutiveCalls($event, $destinations);

        $result = (new QueryEventHandler($dispatcher))(new QueryEvent('order.placed'));

        $this->assertSame($destinations, $result->getDestinations());
    }
}
