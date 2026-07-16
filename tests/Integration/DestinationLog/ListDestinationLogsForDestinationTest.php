<?php

namespace Junction\Api\Test\Integration\DestinationLog;

use Junction\Api\EventLog\EventLog;
use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;

final class ListDestinationLogsForDestinationTest extends TestCase
{
    public function test_list_returns_logs_for_the_destination_with_relations(): void
    {
        $destination = $this->getModelFactory()->create(Destination::class, ['name' => 'My Webhook']);
        $other       = $this->getModelFactory()->create(Destination::class);
        $eventLog    = $this->getModelFactory()->create(EventLog::class);

        $this->getModelFactory()->create(DestinationLog::class, [
            'destination_id' => $destination->id,
            'event_log_id'   => $eventLog->id,
        ]);
        $this->getModelFactory()->create(DestinationLog::class, [
            'destination_id' => $other->id,
            'event_log_id'   => $eventLog->id,
        ]);

        $response = $this->get("/v0/destinations/{$destination->id}/event-logs", [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']);
        $this->assertSame($destination->id, $body['data'][0]['destination']['id']);
        $this->assertSame('My Webhook', $body['data'][0]['destination']['name']);
    }

    public function test_list_returns_not_found_for_unknown_destination_id(): void
    {
        $this->get('/v0/destinations/does-not-exist/event-logs', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_list_requires_a_management_token(): void
    {
        $destination = $this->getModelFactory()->create(Destination::class);

        $this->get("/v0/destinations/{$destination->id}/event-logs")->assertUnauthorized();
    }
}
