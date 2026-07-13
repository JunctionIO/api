<?php

namespace Junction\Api\Test\Integration\Event;

use Junction\Api\Event\Event;
use Junction\Api\Test\Integration\TestCase;

final class ListEventsTest extends TestCase
{
    public function test_list_events_returns_all_events(): void
    {
        $this->mf->create(Event::class);
        $this->mf->create(Event::class);
        $this->mf->create(Event::class);

        $response = $this->get('/v0/events', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk()
                 ->assertAttributeEquals('limit', 25)
                 ->assertAttributeEquals('previous', null)
                 ->assertAttributeEquals('next', null);

        $this->assertCount(3, $response->getResponseBody()['data']);
    }

    public function test_list_events_respects_limit_and_paginates(): void
    {
        $this->mf->create(Event::class);
        $this->mf->create(Event::class);
        $this->mf->create(Event::class);

        $response = $this->get('/v0/events', [
            'X-Junction-Token' => $this->apiToken('management'),
        ], ['limit' => '2']);

        $response->assertOk()
                 ->assertAttributeEquals('limit', 2);

        $body = $response->getResponseBody();

        $this->assertCount(2, $body['data']);
        $this->assertNotNull($body['next']);
    }

    public function test_list_events_requires_a_management_token(): void
    {
        $this->get('/v0/events')->assertUnauthorized();
    }
}
