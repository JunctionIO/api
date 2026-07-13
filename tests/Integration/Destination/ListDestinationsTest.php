<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;

final class ListDestinationsTest extends TestCase
{
    public function test_list_destinations_returns_all_with_relations(): void
    {
        $type = $this->mf->create(DestinationType::class);

        $this->mf->create(Destination::class, ['destination_type_id' => $type->id]);
        $this->mf->create(Destination::class, ['destination_type_id' => $type->id]);

        $response = $this->get('/v0/destinations', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(2, $body['data']);
        $this->assertSame($type->id, $body['data'][0]['destination_type']['id']);
        $this->assertSame([], $body['data'][0]['events']);
    }

    public function test_list_destinations_requires_a_management_token(): void
    {
        $this->get('/v0/destinations')->assertUnauthorized();
    }
}
