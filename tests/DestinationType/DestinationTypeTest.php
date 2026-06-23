<?php

namespace Junction\Api\Test\DestinationType;

use Junction\Api\DestinationType\DestinationType;
use PHPUnit\Framework\TestCase;

final class DestinationTypeTest extends TestCase
{
    public function test_id_getter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $this->assertSame('uuid-123', $type->id);
    }

    public function test_name_getter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $this->assertSame('http', $type->name);
    }

    public function test_name_setter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $type->name = 'webhook';

        $this->assertSame('webhook', $type->name);
    }

    public function test_queue_getter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $this->assertSame('http_queue', $type->queue);
    }

    public function test_queue_setter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $type->queue = 'grpc_queue';

        $this->assertSame('grpc_queue', $type->queue);
    }

    public function test_description_is_nullable(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $this->assertNull($type->description);
    }

    public function test_description_getter(): void
    {
        $type = new DestinationType([
            'id'          => 'uuid-123',
            'name'        => 'http',
            'queue'       => 'http_queue',
            'description' => 'HTTP destination',
        ]);

        $this->assertSame('HTTP destination', $type->description);
    }

    public function test_description_setter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue']);

        $type->description = 'Updated description';

        $this->assertSame('Updated description', $type->description);
    }

    public function test_config_schema_getter(): void
    {
        $schema = [
            'url'    => ['required' => true,  'rules' => ['string', 'url']],
            'method' => ['required' => false, 'rules' => ['string']],
        ];

        $type = new DestinationType([
            'id'            => 'uuid-123',
            'name'          => 'http',
            'queue'         => 'http_queue',
            'config_schema' => $schema,
        ]);

        $this->assertSame($schema, $type->configSchema);
    }

    public function test_config_schema_setter(): void
    {
        $type = new DestinationType(['id' => 'uuid-123', 'name' => 'http', 'queue' => 'http_queue', 'config_schema' => []]);

        $schema = ['url' => ['required' => true, 'rules' => ['string', 'url']]];
        $type->configSchema = $schema;

        $this->assertSame($schema, $type->configSchema);
    }
}
