<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;

final class GetDestinationTest extends TestCase
{
    public function test_get_destination_returns_the_record_with_relations(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $destination = $this->mf->create(Destination::class, [
            'name'                => 'My Webhook',
            'destination_type_id' => $type->id,
        ]);

        $this->get("/v0/destinations/{$destination->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.id', $destination->id)
            ->assertAttributeEquals('data.name', 'My Webhook')
            ->assertAttributeEquals('data.destination_type.id', $type->id)
            ->assertAttributeEquals('data.events', []);
    }

    public function test_get_destination_returns_not_found_for_unknown_id(): void
    {
        $this->get('/v0/destinations/does-not-exist', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_get_destination_requires_a_management_token(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $destination = $this->mf->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->get("/v0/destinations/{$destination->id}")->assertUnauthorized();
    }
}
