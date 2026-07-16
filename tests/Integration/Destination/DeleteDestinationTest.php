<?php

namespace Junction\Api\Test\Integration\Destination;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;

final class DeleteDestinationTest extends TestCase
{
    public function test_delete_destination_removes_the_record(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->delete("/v0/destinations/{$destination->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNoContent();

        $this->get("/v0/destinations/{$destination->id}", [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_delete_destination_returns_not_found_for_unknown_id(): void
    {
        $this->delete('/v0/destinations/does-not-exist', [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertNotFound();
    }

    public function test_delete_destination_requires_a_management_token(): void
    {
        $type = $this->getModelFactory()->create(DestinationType::class);

        $destination = $this->getModelFactory()->create(Destination::class, ['destination_type_id' => $type->id]);

        $this->delete("/v0/destinations/{$destination->id}")->assertUnauthorized();
    }
}
