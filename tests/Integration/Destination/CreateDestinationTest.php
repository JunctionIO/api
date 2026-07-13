<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\DestinationType\DestinationType;

final class CreateDestinationTest extends TestCase
{
    public function test_create_destination_creates_a_destination_with_events(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $response = $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'description'         => 'A test destination',
            'destination_type_id' => $type->id,
            'status'              => 'active',
            'events'              => [['name' => 'user.created'], ['name' => 'user.updated']],
            'config'              => ['url' => 'https://example.com/webhook'],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertCreated()
                 ->assertAttributeEquals('data.name', 'My Webhook')
                 ->assertAttributeEquals('data.status', 'active')
                 ->assertAttributeEquals('data.destination_type.id', $type->id);

        $body = $response->getResponseBody();

        $this->assertCount(2, $body['data']['events']);
        $this->assertSame(
            ['user.created', 'user.updated'],
            array_column($body['data']['events'], 'name')
        );
    }

    public function test_create_destination_requires_a_valid_destination_type_id(): void
    {
        $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'destination_type_id' => 'does-not-exist',
            'status'              => 'active',
            'events'              => [],
            'config'              => [],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_create_destination_requires_a_valid_status(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'destination_type_id' => $type->id,
            'status'              => 'not-a-real-status',
            'events'              => [],
            'config'              => [],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertUnprocessable();
    }

    public function test_create_destination_validates_config_against_the_destination_types_schema(): void
    {
        $type = $this->mf->create(DestinationType::class, [
            'config_schema' => [
                'url' => ['required' => true, 'rules' => ['string']],
            ],
        ]);

        $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'destination_type_id' => $type->id,
            'status'              => 'active',
            'events'              => [],
            'config'              => [],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertUnprocessable()
            ->assertAttributeEquals('errors.0.field', 'config.url');
    }

    public function test_create_destination_requires_a_management_token(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'destination_type_id' => $type->id,
            'status'              => 'active',
            'events'              => [],
            'config'              => [],
        ])->assertUnauthorized();
    }
}
