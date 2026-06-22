<?php

namespace Junction\Api\Test\EventLog\Command;

use PHPUnit\Framework\TestCase;
use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\Command\QueryFind;
use Junction\Api\EventLog\Command\QueryFindHandler;
use Junction\Api\EventLog\EventLogRepositoryInterface;

final class QueryFindHandlerTest extends TestCase
{
    public function test_returns_null_when_log_not_found(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('find')->willReturn(null);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);

        $result = (new QueryFindHandler($repo, $eventRepo))(new QueryFind('log-uuid'));

        $this->assertNull($result);
    }

    public function test_returns_log_with_event_set(): void
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $log   = new EventLog(['id' => 'log-uuid', 'event_id' => 'event-uuid']);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('find')->with('log-uuid')->willReturn($log);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);
        $eventRepo->method('find')->with('event-uuid')->willReturn($event);

        $result = (new QueryFindHandler($repo, $eventRepo))(new QueryFind('log-uuid'));

        $this->assertSame($log, $result);
        $this->assertSame('test.event', $result->getEvent()->name);
    }

    public function test_fetches_event_by_event_id_from_log(): void
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $log   = new EventLog(['id' => 'log-uuid', 'event_id' => 'event-uuid']);

        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('find')->willReturn($log);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);
        $eventRepo->expects($this->once())
            ->method('find')
            ->with('event-uuid')
            ->willReturn($event);

        (new QueryFindHandler($repo, $eventRepo))(new QueryFind('log-uuid'));
    }

    public function test_does_not_fetch_event_when_log_not_found(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->method('find')->willReturn(null);

        $eventRepo = $this->createMock(EventRepositoryInterface::class);
        $eventRepo->expects($this->never())->method('find');

        (new QueryFindHandler($repo, $eventRepo))(new QueryFind('log-uuid'));
    }
}
