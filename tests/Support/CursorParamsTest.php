<?php

namespace Junction\Api\Test\Support;

use PHPUnit\Framework\TestCase;
use Junction\Api\Support\CursorParams;

final class CursorParamsTest extends TestCase
{
    public function test_uses_limit_from_query(): void
    {
        $params = new CursorParams(['limit' => '50']);

        $this->assertSame(50, $params->limit);
    }

    public function test_uses_default_limit_when_absent(): void
    {
        $params = new CursorParams([]);

        $this->assertSame(25, $params->limit);
    }

    public function test_clamps_limit_to_max(): void
    {
        $params = new CursorParams(['limit' => '500']);

        $this->assertSame(100, $params->limit);
    }

    public function test_clamps_limit_to_one_when_zero(): void
    {
        $params = new CursorParams(['limit' => '0']);

        $this->assertSame(1, $params->limit);
    }

    public function test_clamps_limit_to_one_when_negative(): void
    {
        $params = new CursorParams(['limit' => '-10']);

        $this->assertSame(1, $params->limit);
    }

    public function test_casts_non_numeric_limit_to_zero_then_clamps_to_one(): void
    {
        $params = new CursorParams(['limit' => 'abc']);

        $this->assertSame(1, $params->limit);
    }

    public function test_uses_cursor_from_query(): void
    {
        $params = new CursorParams(['cursor' => 'eyJpZCI6MTAwfQ==']);

        $this->assertSame('eyJpZCI6MTAwfQ==', $params->cursor);
    }

    public function test_cursor_is_null_when_absent(): void
    {
        $params = new CursorParams([]);

        $this->assertNull($params->cursor);
    }

    public function test_respects_custom_default_and_max(): void
    {
        $params = new CursorParams([], 10, 50);

        $this->assertSame(10, $params->limit);
    }

    public function test_clamps_to_custom_max(): void
    {
        $params = new CursorParams(['limit' => '200'], 10, 50);

        $this->assertSame(50, $params->limit);
    }
}
