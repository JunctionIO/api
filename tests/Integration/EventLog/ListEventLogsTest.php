<?php

namespace Junction\Api\Test\Integration\EventLog;

use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Test\Integration\TestCase;

final class ListEventLogsTest extends TestCase
{
    public function test_list_event_logs_returns_all_with_event_relation(): void
    {
        $event = $this->mf->create(Event::class, ['name' => 'user.created']);

        $this->mf->create(EventLog::class, ['event_id' => $event->id]);
        $this->mf->create(EventLog::class, ['event_id' => $event->id]);

        $response = $this->get('/v0/event-logs', [
            'X-Junction-Token' => $this->apiToken('management'),
        ]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(2, $body['data']);
        $this->assertSame($event->id, $body['data'][0]['event']['id']);
        $this->assertSame('user.created', $body['data'][0]['event']['name']);
    }

    public function test_list_event_logs_filters_by_event_name(): void
    {
        $created = $this->mf->create(Event::class, ['name' => 'user.created']);
        $deleted = $this->mf->create(Event::class, ['name' => 'user.deleted']);

        $this->mf->create(EventLog::class, ['event_id' => $created->id]);
        $this->mf->create(EventLog::class, ['event_id' => $deleted->id]);

        $response = $this->get('/v0/event-logs', [
            'X-Junction-Token' => $this->apiToken('management'),
        ], ['filter' => ['event' => ['user.created']]]);

        $response->assertOk();

        $body = $response->getResponseBody();

        $this->assertCount(1, $body['data']);
        $this->assertSame('user.created', $body['data'][0]['event']['name']);
    }

    public function test_list_event_logs_filter_with_unknown_event_name_returns_empty(): void
    {
        $event = $this->mf->create(Event::class);

        $this->mf->create(EventLog::class, ['event_id' => $event->id]);

        $response = $this->get('/v0/event-logs', [
            'X-Junction-Token' => $this->apiToken('management'),
        ], ['filter' => ['event' => ['does.not.exist']]]);

        $response->assertOk();

        $this->assertCount(0, $response->getResponseBody()['data']);
    }

    public function test_list_event_logs_requires_a_management_token(): void
    {
        $this->get('/v0/event-logs')->assertUnauthorized();
    }
}
