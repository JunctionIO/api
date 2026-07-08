<?php

namespace Junction\Api\Test\Unit\EventLog\Command;

use Junction\Api\EventLog\Command\Create;
use Junction\Api\EventLog\Command\CreateHandler;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\EventLogRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateHandlerTest extends TestCase
{
    private function makeCommand(
        string $traceId = 'trace-uuid',
        string $eventId = 'event-uuid',
        string $authId = 'auth-id',
        ?string $sourceIp = '127.0.0.1',
        array $payload = ['key' => 'value'],
    ): Create {
        return new Create($traceId, $eventId, $authId, $sourceIp, $payload);
    }

    public function test_returns_event_log(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand());

        $this->assertInstanceOf(EventLog::class, $result);
    }

    public function test_sets_trace_id(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(traceId: 'trace-xyz'));

        $this->assertSame('trace-xyz', $result->traceId);
    }

    public function test_sets_event_id(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(eventId: 'event-xyz'));

        $this->assertSame('event-xyz', $result->eventId);
    }

    public function test_sets_auth_id(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(authId: 'auth-xyz'));

        $this->assertSame('auth-xyz', $result->authId);
    }

    public function test_sets_source_ip(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(sourceIp: '10.0.0.1'));

        $this->assertSame('10.0.0.1', $result->sourceIp);
    }

    public function test_accepts_null_source_ip(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(sourceIp: null));

        $this->assertNull($result->sourceIp);
    }

    public function test_sets_payload(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand(payload: ['foo' => 'bar']));

        $this->assertSame(['foo' => 'bar'], $result->payload);
    }

    public function test_sets_received_at(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand());

        $this->assertInstanceOf(\DateTimeInterface::class, $result->receivedAt);
    }

    public function test_initializes_created_at(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);

        $result = (new CreateHandler($repo))($this->makeCommand());

        $this->assertInstanceOf(\DateTimeInterface::class, $result->createdAt);
    }

    public function test_calls_save_on_repository(): void
    {
        $repo = $this->createMock(EventLogRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(EventLog::class));

        (new CreateHandler($repo))($this->makeCommand());
    }
}
