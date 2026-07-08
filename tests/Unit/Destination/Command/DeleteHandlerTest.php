<?php

namespace Junction\Api\Test\Unit\Destination\Command;

use Junction\Api\Destination\Command\Delete;
use Junction\Api\Destination\Command\DeleteHandler;
use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

final class DeleteHandlerTest extends TestCase
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

    public function test_calls_find_or_fail_with_command_id(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('dest-uuid')
            ->willReturn($dest);
        $repo->method('clearEvents')->willReturn(0);
        $repo->method('delete')->willReturn(true);

        (new DeleteHandler($repo))(new Delete('dest-uuid'));
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);

        (new DeleteHandler($repo))(new Delete('dest-uuid'));
    }

    public function test_clears_events_for_destination(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);
        $repo->expects($this->once())
            ->method('clearEvents')
            ->with('dest-uuid')
            ->willReturn(0);
        $repo->method('delete')->willReturn(true);

        (new DeleteHandler($repo))(new Delete('dest-uuid'));
    }

    public function test_deletes_destination(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);
        $repo->method('clearEvents')->willReturn(0);
        $repo->expects($this->once())
            ->method('delete')
            ->with($dest)
            ->willReturn(true);

        (new DeleteHandler($repo))(new Delete('dest-uuid'));
    }

    public function test_clears_events_before_deleting(): void
    {
        $dest      = $this->makeDestination();
        $callOrder = [];

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);
        $repo->method('clearEvents')->willReturnCallback(function () use (&$callOrder) {
            $callOrder[] = 'clearEvents';
            return 0;
        });
        $repo->method('delete')->willReturnCallback(function () use (&$callOrder) {
            $callOrder[] = 'delete';
            return true;
        });

        (new DeleteHandler($repo))(new Delete('dest-uuid'));

        $this->assertSame(['clearEvents', 'delete'], $callOrder);
    }
}
