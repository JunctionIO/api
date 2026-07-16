<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;

final class UpdateDestinationEventsTest extends TestCase
{
    public function test_put_destination_events_replaces_the_event_set(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->put("/v0/destinations/{$destination->id}/events", [
            'events' => [['name' => 'user.created'], ['name' => 'user.deleted']],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response = $this->put("/v0/destinations/{$destination->id}/events", [
            'events' => [['name' => 'user.updated']],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']['events']);
        $this->assertSame('user.updated', $body['data']['events'][0]['name']);
    }

    public function test_put_destination_events_requires_an_events_array(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->put("/v0/destinations/{$destination->id}/events", [], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertUnprocessableContent();
    }

    public function test_put_destination_events_returns_not_found_for_unknown_id(): void
    {
        $this->put('/v0/destinations/does-not-exist/events', [
            'events' => [['name' => 'user.created']],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_put_destination_events_requires_a_management_token(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->put("/v0/destinations/{$destination->id}/events", [
            'events' => [['name' => 'user.created']],
        ])->assertUnauthorized();
    }
}
