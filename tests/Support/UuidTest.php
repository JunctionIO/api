<?php

namespace Junction\Api\Test\Support;

use PHPUnit\Framework\TestCase;
use Junction\Api\Support\Uuid;

final class UuidTest extends TestCase
{
    public function test_v4_returns_valid_uuid(): void
    {
        $uuid = Uuid::v4();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function test_v4_returns_unique_values(): void
    {
        $this->assertNotSame(Uuid::v4(), Uuid::v4());
    }
}
