<?php

namespace Junction\Api\Test\Integration\DestinationType;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\DestinationType\DestinationType;

final class GetDestinationTypeTest extends TestCase
{
    public function test_get_destination_type_returns_the_record(): void
    {
        $destinationType = $this->mf->create(DestinationType::class, [
            'name' => 'http',
        ]);

        $this->get("/v0/destination-types/{$destinationType->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])
            ->assertOk()
            ->assertAttributeEquals('data.id', $destinationType->id)
            ->assertAttributeEquals('data.name', 'http');
    }

    public function test_get_destination_type_returns_not_found_for_unknown_id(): void
    {
        $this->get('/v0/destination-types/does-not-exist', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_get_destination_type_requires_a_management_token(): void
    {
        $destinationType = $this->mf->create(DestinationType::class);

        $this->get("/v0/destination-types/{$destinationType->id}")->assertUnauthorized();
    }
}
