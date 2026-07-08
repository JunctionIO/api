<?php

namespace Junction\Api\Test\Unit\ApiToken;

use Junction\Api\ApiToken\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    private Token $token;

    protected function setUp(): void
    {
        $this->token = new Token('prod-relay', 'relay', 1700000000);
    }

    public function test_stores_id(): void
    {
        $this->assertSame('prod-relay', $this->token->id);
    }

    public function test_stores_type(): void
    {
        $this->assertSame('relay', $this->token->type);
    }

    public function test_stores_issued_at(): void
    {
        $this->assertSame(1700000000, $this->token->issuedAt);
    }

    public function test_is_type_returns_true_for_matching_type(): void
    {
        $this->assertTrue($this->token->isType('relay'));
    }

    public function test_is_type_returns_false_for_non_matching_type(): void
    {
        $this->assertFalse($this->token->isType('management'));
    }
}
