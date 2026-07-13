<?php

namespace Junction\Api\Test\Integration\DestinationLog;

use Junction\Api\EventLog\EventLog;
use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;

final class ListDestinationLogsForEventLogTest extends TestCase
{
    public function test_list_returns_logs_for_the_event_log_with_relations(): void
    {
        $eventLog    = $this->mf->create(EventLog::class);
        $other       = $this->mf->create(EventLog::class);
        $destination = $this->mf->create(Destination::class);

        $this->mf->create(DestinationLog::class, [
            'event_log_id'   => $eventLog->id,
            'destination_id' => $destination->id,
        ]);
        $this->mf->create(DestinationLog::class, [
            'event_log_id'   => $other->id,
            'destination_id' => $destination->id,
        ]);

        $response = $this->get("/v0/event-logs/{$eventLog->id}/destinations", [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']);
        $this->assertSame($eventLog->id, $body['data'][0]['event_log']['id']);
    }

    public function test_list_returns_not_found_for_unknown_event_log_id(): void
    {
        $this->get('/v0/event-logs/does-not-exist/destinations', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_list_requires_a_management_token(): void
    {
        $eventLog = $this->mf->create(EventLog::class);

        $this->get("/v0/event-logs/{$eventLog->id}/destinations")->assertUnauthorized();
    }
}
