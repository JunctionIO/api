<?php

namespace Junction\Api\Test\Integration\DestinationLog;

use Junction\Api\EventLog\EventLog;
use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;

final class UpdateDestinationLogStatusTest extends TestCase
{
    private function attemptedAt(): string
    {
        return (new \DateTimeImmutable())->format('Y-m-d\TH:i:sp');
    }

    public function test_update_status_updates_a_destination_log(): void
    {
        $eventLog    = $this->getModelFactory()->create(EventLog::class);
        $destination = $this->getModelFactory()->create(Destination::class);

        $log = $this->getModelFactory()->create(DestinationLog::class, [
            'event_log_id'   => $eventLog->id,
            'destination_id' => $destination->id,
            'status'         => 'pending',
        ]);

        $attemptedAt = $this->attemptedAt();

        $this->post('/system/status', [
            'log_id'       => $log->id,
            'status'       => 'dispatched',
            'attempted_at' => $attemptedAt,
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $response = $this->get("/v0/event-logs/{$log->eventLogId}/destinations", [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertSame('dispatched', $body['data'][0]['status']);
        $this->assertNull($body['data'][0]['error']);
    }

    public function test_update_status_records_an_error(): void
    {
        $eventLog    = $this->getModelFactory()->create(EventLog::class);
        $destination = $this->getModelFactory()->create(Destination::class);

        $log = $this->getModelFactory()->create(DestinationLog::class, [
            'event_log_id'   => $eventLog->id,
            'destination_id' => $destination->id,
            'status'         => 'pending',
        ]);

        $this->post('/system/status', [
            'log_id'       => $log->id,
            'status'       => 'errored',
            'attempted_at' => $this->attemptedAt(),
            'error'        => 'connection timed out',
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $response = $this->get("/v0/event-logs/{$log->eventLogId}/destinations", [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $body = $response->getResponseBody();

        $this->assertSame('errored', $body['data'][0]['status']);
        $this->assertSame('connection timed out', $body['data'][0]['error']);
    }

    public function test_update_status_requires_a_valid_status(): void
    {
        $log = $this->getModelFactory()->create(DestinationLog::class);

        $this->post('/system/status', [
            'log_id'       => $log->id,
            'status'       => 'not-a-real-status',
            'attempted_at' => $this->attemptedAt(),
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertUnprocessableContent();
    }

    public function test_update_status_requires_a_valid_attempted_at_format(): void
    {
        $log = $this->getModelFactory()->create(DestinationLog::class);

        $this->post('/system/status', [
            'log_id'       => $log->id,
            'status'       => 'dispatched',
            'attempted_at' => '2026-07-13 12:00:00',
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertUnprocessableContent();
    }

    public function test_update_status_returns_not_found_for_unknown_log_id(): void
    {
        $this->post('/system/status', [
            'log_id'       => 'does-not-exist',
            'status'       => 'dispatched',
            'attempted_at' => $this->attemptedAt(),
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNotFound();
    }

    public function test_update_status_requires_a_system_token(): void
    {
        $log = $this->getModelFactory()->create(DestinationLog::class);

        $this->post('/system/status', [
            'log_id'       => $log->id,
            'status'       => 'dispatched',
            'attempted_at' => $this->attemptedAt(),
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertUnauthorized();
    }
}
