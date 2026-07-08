<?php

namespace Junction\Api\Test\Unit\DestinationLog\Command;

use Junction\Api\DestinationLog\Command\Update;
use Junction\Api\DestinationLog\Command\UpdateHandler;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationLog\DestinationLogRepositoryInterface;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

final class UpdateHandlerTest extends TestCase
{
    private function makeLog(): DestinationLog
    {
        return new DestinationLog([
            'id'             => 'dlog-uuid',
            'trace_id'       => 'trace-uuid',
            'event_log_id'   => 'elog-uuid',
            'destination_id' => 'dest-uuid',
            'status'         => 'pending',
            'created_at'     => '2026-06-26 10:00:00',
            'updated_at'     => '2026-06-26 10:00:00',
        ]);
    }

    private function makeCommand(): Update
    {
        return new Update(
            id: 'dlog-uuid',
            status: 'dispatched',
            attemptedAt: '2026-06-26T10:00:00Z',
        );
    }

    private function makeRepo(DestinationLog $log): DestinationLogRepositoryInterface
    {
        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($log);
        $repo->method('save')->willReturn(true);

        return $repo;
    }

    public function test_returns_destination_log(): void
    {
        $result = (new UpdateHandler($this->makeRepo($this->makeLog())))($this->makeCommand());

        $this->assertInstanceOf(DestinationLog::class, $result);
    }

    public function test_calls_find_or_fail_with_id(): void
    {
        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('dlog-uuid')
            ->willReturn($this->makeLog());
        $repo->method('save')->willReturn(true);

        (new UpdateHandler($repo))($this->makeCommand());
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);

        (new UpdateHandler($repo))($this->makeCommand());
    }

    public function test_saves_model(): void
    {
        $repo = $this->createMock(DestinationLogRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeLog());
        $repo->expects($this->once())->method('save')->willReturn(true);

        (new UpdateHandler($repo))($this->makeCommand());
    }

    public function test_sets_status(): void
    {
        $result = (new UpdateHandler($this->makeRepo($this->makeLog())))($this->makeCommand());

        $this->assertSame('dispatched', $result->status);
    }

    public function test_sets_attempted_at(): void
    {
        $result = (new UpdateHandler($this->makeRepo($this->makeLog())))($this->makeCommand());

        $this->assertInstanceOf(\DateTimeInterface::class, $result->attemptedAt);
        $this->assertSame('2026-06-26 10:00:00', $result->attemptedAt->format('Y-m-d H:i:s'));
    }

    public function test_sets_error(): void
    {
        $command = new Update('dlog-uuid', 'errored', '2026-06-26T10:00:00Z', 'Connection refused');

        $result = (new UpdateHandler($this->makeRepo($this->makeLog())))($command);

        $this->assertSame('Connection refused', $result->error);
    }

    public function test_sets_null_error_when_not_provided(): void
    {
        $result = (new UpdateHandler($this->makeRepo($this->makeLog())))($this->makeCommand());

        $this->assertNull($result->error);
    }

}
