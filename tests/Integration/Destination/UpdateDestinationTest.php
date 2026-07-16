<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;

final class UpdateDestinationTest extends TestCase
{
    public function test_patch_destination_updates_name_and_status(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, [
            'name'                => 'original',
            'status'              => 'active',
            'destination_type_id' => $type->id,
        ]);

        $this->patch("/v0/destinations/{$destination->id}", [
            'name'   => 'updated',
            'status' => 'disabled',
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.name', 'updated')
            ->assertAttributeEquals('data.status', 'disabled');
    }

    public function test_patch_destination_validates_config_against_the_destination_types_schema(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class, [
            'config_schema' => [
                'url' => ['required' => true, 'rules' => ['string']],
            ],
        ]);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->patch("/v0/destinations/{$destination->id}", [
            'config' => [],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertUnprocessableContent()
            ->assertAttributeEquals('errors.0.field', 'config.url');

        $this->patch("/v0/destinations/{$destination->id}", [
            'config' => ['url' => 'https://example.com/webhook'],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.config.url', 'https://example.com/webhook');
    }

    public function test_patch_destination_returns_not_found_for_unknown_id(): void
    {
        $this->patch('/v0/destinations/does-not-exist', ['name' => 'updated'], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_patch_destination_requires_a_management_token(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->patch("/v0/destinations/{$destination->id}", ['name' => 'updated'])->assertUnauthorized();
    }
}
