<?php

namespace Junction\Api\Test\Unit\DestinationType\Command;

use Junction\Api\DestinationType\Command\Upsert;
use Junction\Api\DestinationType\Command\UpsertHandler;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class UpsertHandlerTest extends TestCase
{
    private function makeExistingType(): DestinationType
    {
        return new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'webhook',
            'queue'         => 'junction.destinations.webhook',
            'description'   => 'Old Description',
            'config_schema' => [],
            'created_at'    => '2026-06-25 10:00:00',
            'updated_at'    => '2026-06-25 10:00:00',
        ]);
    }

    private function makeCommand(): Upsert
    {
        return new Upsert(
            name: 'webhook',
            queue: 'webhook',
            description: 'My Webhook',
            configSchema: ['url' => ['required' => true, 'rules' => ['string', 'url']]],
        );
    }

    private function makeRepo(?DestinationType $existing = null): DestinationTypeRepositoryInterface
    {
        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findBy')->willReturn($existing);
        $repo->method('save')->willReturn(true);

        return $repo;
    }

    public function test_returns_destination_type(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))($this->makeCommand());

        $this->assertInstanceOf(DestinationType::class, $result);
    }

    public function test_looks_up_by_name_column(): void
    {
        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findBy')
            ->with('name', 'webhook')
            ->willReturn(null);
        $repo->method('save')->willReturn(true);

        (new UpsertHandler($repo))($this->makeCommand());
    }

    public function test_saves_model(): void
    {
        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findBy')->willReturn(null);
        $repo->expects($this->once())->method('save')->willReturn(true);

        (new UpsertHandler($repo))($this->makeCommand());
    }

    public function test_saves_existing_model_when_found(): void
    {
        $existing = $this->makeExistingType();

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findBy')->willReturn($existing);
        $repo->expects($this->once())->method('save')->with($existing)->willReturn(true);

        (new UpsertHandler($repo))($this->makeCommand());
    }

    public function test_sets_name(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))($this->makeCommand());

        $this->assertSame('webhook', $result->name);
    }

    public function test_sets_description(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))($this->makeCommand());

        $this->assertSame('My Webhook', $result->description);
    }

    public function test_sets_queue(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))($this->makeCommand());

        $this->assertSame('junction.destinations.webhook', $result->queue);
    }

    public function test_sets_config_schema(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))($this->makeCommand());

        $this->assertSame(
            ['url' => ['required' => true, 'rules' => ['string', 'url']]],
            $result->configSchema
        );
    }

    public function test_updates_description_on_existing_model(): void
    {
        $existing = $this->makeExistingType();

        $result = (new UpsertHandler($this->makeRepo($existing)))($this->makeCommand());

        $this->assertSame('My Webhook', $result->description);
    }

    public function test_updates_config_schema_on_existing_model(): void
    {
        $existing = $this->makeExistingType();

        $result = (new UpsertHandler($this->makeRepo($existing)))($this->makeCommand());

        $this->assertSame(
            ['url' => ['required' => true, 'rules' => ['string', 'url']]],
            $result->configSchema
        );
    }

    public function test_sets_null_description_when_not_provided(): void
    {
        $result = (new UpsertHandler($this->makeRepo()))(new Upsert('webhook', 'webhook'));

        $this->assertNull($result->description);
    }
}
