<?php

namespace Junction\Api\Test\Integration\Relay;

use Meritum\Database\Support\Uuid;
use Junction\Api\Test\Integration\TestCase;
use Junction\Api\DestinationType\DestinationType;

final class PostRelayTest extends TestCase
{
    public function test_relay_publishes_a_message_to_the_subscribed_destinations_queue(): void
    {
        $queue = $this->getMemoryQueue();

        $type = $this->getModelFactory()->create(DestinationType::class, ['name' => 'http', 'queue' => 'http']);

        $this->post('/v0/destinations', [
            'name'                => 'My Webhook',
            'destination_type_id' => $type->id,
            'status'              => 'active',
            'events'              => [['name' => 'user.created']],
            'config'              => ['url' => 'https://example.com/webhook'],
        ], [
            'X-Junction-Token' => $this->apiToken('management'),
        ])->assertCreated();

        $response = $this->post('/relay', ['payload' => ['id' => 'abc123']], [
            'X-Junction-Token' => $this->apiToken('relay'),
            'X-Junction-Event' => 'user.created',
        ]);

        $response->assertAccepted();

        $this->assertNotSame('', $response->getResponse()->getHeaderLine('X-Junction-Trace-ID'));

        $messages = $queue->getMessages('http');

        $this->assertCount(1, $messages);

        $message = $messages[0];

        $this->assertSame(['id' => 'abc123'], $message->payload);
        $this->assertSame($type->name, $message->meta['destination']['type']);
        $this->assertSame(['url' => 'https://example.com/webhook'], $message->meta['destination']['config']);
        $this->assertNotSame('', $message->meta['trace_id']);
        $this->assertNotSame('', $message->meta['log_id']);
    }

    public function test_relay_is_a_noop_for_an_event_with_no_subscribers(): void
    {
        $queue = $this->getMemoryQueue();

        $this->post('/relay', ['payload' => ['id' => 'abc123']], [
            'X-Junction-Token' => $this->apiToken('relay'),
            'X-Junction-Event' => 'nobody.listening',
        ])->assertAccepted();

        $this->assertSame([], $queue->getQueues());
    }

    public function test_relay_echoes_a_provided_trace_id(): void
    {
        $this->getMemoryQueue();

        $traceId = Uuid::v4();

        $response = $this->post('/relay', ['payload' => []], [
            'X-Junction-Token' => $this->apiToken('relay'),
            'X-Junction-Event' => 'user.created',
            'X-Junction-Trace-ID' => $traceId,
        ]);

        $response->assertAccepted();

        $this->assertSame($traceId, $response->getResponse()->getHeaderLine('X-Junction-Trace-ID'));
    }

    public function test_relay_requires_the_event_header(): void
    {
        $this->post('/relay', ['payload' => []], [
            'X-Junction-Token' => $this->apiToken('relay'),
        ])->assertBadRequest();
    }

    public function test_relay_requires_a_valid_event_header_format(): void
    {
        $this->post('/relay', ['payload' => []], [
            'X-Junction-Token' => $this->apiToken('relay'),
            'X-Junction-Event' => 'not a valid event!',
        ])->assertBadRequest();
    }

    public function test_relay_requires_a_payload_array(): void
    {
        $this->post('/relay', [], [
            'X-Junction-Token' => $this->apiToken('relay'),
            'X-Junction-Event' => 'user.created',
        ])->assertUnprocessableContent();
    }

    public function test_relay_requires_a_relay_token(): void
    {
        $this->post('/relay', ['payload' => []], [
            'X-Junction-Token' => $this->apiToken('management'),
            'X-Junction-Event' => 'user.created',
        ])->assertUnauthorized();
    }
}
