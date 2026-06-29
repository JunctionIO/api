<?php

namespace Junction\Api\Test\Relay\Command;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\Command\CreateMany;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Queue\QueueInterface;
use Junction\Api\Relay\Command\Relay;
use Junction\Api\Relay\Command\RelayHandler;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;

final class RelayHandlerTest extends TestCase
{
    private function makeDestination(string $id = 'dest-uuid', string $queue = 'junction.http'): Destination
    {
        $dest = new Destination([
            'id'                  => $id,
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => ['url' => 'https://example.com'],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);

        $dest->setDestinationType(new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => $queue,
            'config_schema' => [],
        ]));

        return $dest;
    }

    private function makeCommand(
        ?Collection $destinations = null,
        string $traceId = 'trace-uuid',
        string $logId = 'log-uuid',
        array $payload = ['foo' => 'bar'],
    ): Relay {
        return new Relay($traceId, $logId, $payload, $destinations ?? new Collection([]));
    }

    private function makeHandler(?QueueInterface $queue = null, ?DispatcherInterface $dispatcher = null): RelayHandler
    {
        return new RelayHandler(
            $queue      ?? $this->createMock(QueueInterface::class),
            $dispatcher ?? $this->createMock(DispatcherInterface::class),
        );
    }

    public function test_does_not_publish_when_destinations_is_empty(): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->never())->method('publish');

        $this->makeHandler($queue)($this->makeCommand());
    }

    public function test_does_not_dispatch_when_destinations_is_empty(): void
    {
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $this->makeHandler(dispatcher: $dispatcher)($this->makeCommand());
    }

    public function test_publishes_to_queue_for_each_destination(): void
    {
        $destinations = new Collection([
            'dest-1' => $this->makeDestination('dest-1'),
            'dest-2' => $this->makeDestination('dest-2'),
        ]);

        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->exactly(2))->method('publish');

        $this->makeHandler($queue)($this->makeCommand($destinations));
    }

    public function test_publishes_using_destination_type_queue_name(): void
    {
        $dest = $this->makeDestination('dest-1', 'junction.destinations.webhook');
        $destinations = new Collection(['dest-1' => $dest]);

        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->once())
            ->method('publish')
            ->with('junction.destinations.webhook', $this->anything());

        $this->makeHandler($queue)($this->makeCommand($destinations));
    }

    public function test_dispatches_create_many_with_correct_trace_id(): void
    {
        $destinations = new Collection(['dest-1' => $this->makeDestination()]);
        $captured     = null;

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($cmd) use (&$captured) {
            $captured = $cmd;
        });

        $this->makeHandler(dispatcher: $dispatcher)($this->makeCommand($destinations, traceId: 'my-trace'));

        $this->assertInstanceOf(CreateMany::class, $captured);
        $this->assertSame('my-trace', $captured->traceId);
    }

    public function test_dispatches_create_many_with_correct_log_id(): void
    {
        $destinations = new Collection(['dest-1' => $this->makeDestination()]);
        $captured     = null;

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($cmd) use (&$captured) {
            $captured = $cmd;
        });

        $this->makeHandler(dispatcher: $dispatcher)($this->makeCommand($destinations, logId: 'my-log'));

        $this->assertSame('my-log', $captured->eventLogId);
    }

    public function test_dispatches_create_many_with_destination_ids(): void
    {
        $destinations = new Collection([
            'dest-1' => $this->makeDestination('dest-1'),
            'dest-2' => $this->makeDestination('dest-2'),
        ]);
        $captured = null;

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($cmd) use (&$captured) {
            $captured = $cmd;
        });

        $this->makeHandler(dispatcher: $dispatcher)($this->makeCommand($destinations));

        $this->assertSame(['dest-1', 'dest-2'], $captured->destinationIds);
    }
}
