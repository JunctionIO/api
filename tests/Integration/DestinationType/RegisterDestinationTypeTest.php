<?php

namespace Junction\Api\Test\Integration\DestinationType;

use Junction\Api\Test\Integration\TestCase;
use Junction\Api\Test\Integration\Queue\MemoryQueue;

final class RegisterDestinationTypeTest extends TestCase
{
    private const array PAYLOAD = [
        'name'          => 'http',
        'description'   => 'Outbound HTTP webhook delivery',
        'queue'         => 'http',
        'config_schema' => [
            'url' => ['required' => true, 'rules' => ['string']],
        ],
    ];

    private MemoryQueue $queue;

    protected function setUp(): void
    {
        parent::setUp();

        // The route's DeclareQueue middleware resolves QueueInterface unconditionally
        // on a successful upsert; swap in the fake for every test in this class.
        $this->queue = $this->getMemoryQueue();
    }

    public function test_register_creates_a_new_destination_type(): void
    {
        $this->put('/system/destination-types/register', self::PAYLOAD, [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $response = $this->get('/v0/destination-types', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']);
        $this->assertSame('http', $body['data'][0]['name']);
        $this->assertSame('junction.destinations.http', $body['data'][0]['queue']);
        $this->assertSame('Outbound HTTP webhook delivery', $body['data'][0]['description']);
        $this->assertSame(self::PAYLOAD['config_schema'], $body['data'][0]['config_schema']);
    }

    public function test_register_upserts_an_existing_destination_type_by_name(): void
    {
        $this->put('/system/destination-types/register', self::PAYLOAD, [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $this->put('/system/destination-types/register', [
            ...self::PAYLOAD,
            'description' => 'Updated description',
            'queue'       => 'http-v2',
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $response = $this->get('/v0/destination-types', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']);
        $this->assertSame('junction.destinations.http-v2', $body['data'][0]['queue']);
        $this->assertSame('Updated description', $body['data'][0]['description']);
    }

    public function test_register_declares_the_destination_queue(): void
    {
        $this->put('/system/destination-types/register', self::PAYLOAD, [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertNoContent();

        $this->assertTrue($this->queue->has('junction.destinations.http'));
    }

    public function test_register_requires_a_system_token(): void
    {
        $this->put('/system/destination-types/register', self::PAYLOAD, [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertUnauthorized();
    }

    public function test_register_requires_a_name(): void
    {
        $this->put('/system/destination-types/register', [
            ...self::PAYLOAD,
            'name' => '',
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertUnprocessable();
    }

    public function test_register_requires_a_valid_queue_format(): void
    {
        $this->put('/system/destination-types/register', [
            ...self::PAYLOAD,
            'queue' => 'not a valid queue name!',
        ], [
            'X-Junction-Token' => $this->apiToken('system'),
        ])->assertUnprocessable();
    }
}
