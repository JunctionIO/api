<?php

namespace Junction\Api\Test\Integration\EventLog;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Test\Integration\TestCase;

final class GetEventLogTest extends TestCase
{
    public function test_get_event_log_returns_the_record_with_event(): void
    {
        $event = $this->mf->create(Event::class, ['name' => 'user.created']);

        $log = $this->mf->create(EventLog::class, [
            'event_id' => $event->id,
            'source_ip' => '203.0.113.5',
        ]);

        $this->get("/v0/event-logs/{$log->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.id', $log->id)
            ->assertAttributeEquals('data.source_ip', '203.0.113.5')
            ->assertAttributeEquals('data.event.id', $event->id)
            ->assertAttributeEquals('data.event.name', 'user.created');
    }

    public function test_get_event_log_returns_not_found_for_unknown_id(): void
    {
        $this->get('/v0/event-logs/does-not-exist', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_get_event_log_requires_a_management_token(): void
    {
        $event = $this->mf->create(Event::class);

        $log = $this->mf->create(EventLog::class, ['event_id' => $event->id]);

        $this->get("/v0/event-logs/{$log->id}")->assertUnauthorized();
    }
}
