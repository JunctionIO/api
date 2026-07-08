<?php

namespace Junction\Api\Test\Unit\Trace;

use PHPUnit\Framework\TestCase;
use Junction\Api\Trace\TraceId;

final class TraceIdTest extends TestCase
{
    public function test_generates_uuid_on_construction(): void
    {
        $id = new TraceId();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $id->id
        );
    }

    public function test_set_replaces_id(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $id = (new TraceId())->set($uuid);

        $this->assertSame($uuid, $id->id);
    }

    public function test_set_returns_self(): void
    {
        $id = new TraceId();

        $this->assertSame($id, $id->set('550e8400-e29b-41d4-a716-446655440000'));
    }

    public function test_casts_to_string(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $id = (new TraceId())->set($uuid);

        $this->assertSame($uuid, (string) $id);
    }
}
