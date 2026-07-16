<?php

namespace Junction\Api\Test\Integration\Event;

use Junction\Api\Event\Event;
use Junction\Api\Test\Integration\TestCase;

final class GetEventTest extends TestCase
{
    public function test_get_event_returns_the_event(): void
    {
        $event = $this->getModelFactory()->create(Event::class, [
            'name'        => 'user.created',
            'description' => 'Fires when a user is created',
        ]);

        $this->get("/v0/events/{$event->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.id', $event->id)
            ->assertAttributeEquals('data.name', 'user.created')
            ->assertAttributeEquals('data.description', 'Fires when a user is created');
    }

    public function test_get_event_returns_not_found_for_unknown_id(): void
    {
        $this->get('/v0/events/does-not-exist', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertNotFound();
    }
}
