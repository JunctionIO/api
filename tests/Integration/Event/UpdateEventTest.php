<?php

namespace Junction\Api\Test\Integration\Event;

use Junction\Api\Event\Event;
use Junction\Api\Test\Integration\TestCase;

final class UpdateEventTest extends TestCase
{
    public function test_patch_event_updates_the_description(): void
    {
        $event = $this->mf->create(Event::class, ['description' => 'original']);

        $this->patch("/v0/events/{$event->id}", ['description' => 'updated'], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.id', $event->id)
            ->assertAttributeEquals('data.description', 'updated');
    }

    public function test_patch_event_allows_a_null_description(): void
    {
        $event = $this->mf->create(Event::class, ['description' => 'original']);

        $this->patch("/v0/events/{$event->id}", ['description' => null], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.description', null);
    }

    public function test_patch_event_requires_a_description_key(): void
    {
        $event = $this->mf->create(Event::class);

        $this->patch("/v0/events/{$event->id}", [], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertUnprocessable()
            ->assertAttributeEquals('errors.0.field', 'description');
    }

    public function test_patch_event_returns_not_found_for_unknown_id(): void
    {
        $this->patch('/v0/events/does-not-exist', ['description' => 'updated'], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertNotFound();
    }
}
