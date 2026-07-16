<?php

namespace Junction\Api\Test\Integration\DestinationType;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\DestinationType\DestinationType;

final class ListDestinationTypesTest extends TestCase
{
    public function test_list_destination_types_returns_all_records(): void
    {
        $this->getModelFactory()->create(DestinationType::class);
        $this->getModelFactory()->create(DestinationType::class);

        $response = $this->get('/v0/destination-types', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $this->assertCount(2, $response->getResponseBody()['data']);
    }

    public function test_list_destination_types_requires_a_management_token(): void
    {
        $this->get('/v0/destination-types')->assertUnauthorized();
    }
}
