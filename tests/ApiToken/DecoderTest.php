<?php

namespace Junction\Api\Test\ApiToken;

use Firebase\JWT\JWT;
use Junction\Api\ApiToken\Decoder;
use Junction\Api\ApiToken\InvalidTokenException;
use Junction\Api\ApiToken\Token;
use PHPUnit\Framework\TestCase;

final class DecoderTest extends TestCase
{
    private string $secret = 'test-secret-key-for-unit-tests-min32';

    private function makeJwt(array $payload): string
    {
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    private function validPayload(): array
    {
        return ['id' => 'my-relay', 'type' => 'relay', 'iat' => 1700000000];
    }

    public function test_returns_token_instance(): void
    {
        $jwt = $this->makeJwt($this->validPayload());

        $this->assertInstanceOf(Token::class, (new Decoder($this->secret))->decode($jwt));
    }

    public function test_returns_token_with_correct_id(): void
    {
        $jwt   = $this->makeJwt($this->validPayload());
        $token = (new Decoder($this->secret))->decode($jwt);

        $this->assertSame('my-relay', $token->id);
    }

    public function test_returns_token_with_correct_type(): void
    {
        $jwt   = $this->makeJwt($this->validPayload());
        $token = (new Decoder($this->secret))->decode($jwt);

        $this->assertSame('relay', $token->type);
    }

    public function test_returns_token_with_correct_issued_at(): void
    {
        $jwt   = $this->makeJwt($this->validPayload());
        $token = (new Decoder($this->secret))->decode($jwt);

        $this->assertSame(1700000000, $token->issuedAt);
    }

    public function test_throws_for_invalid_signature(): void
    {
        $jwt = $this->makeJwt($this->validPayload());

        $this->expectException(InvalidTokenException::class);

        (new Decoder('wrong-secret-key-that-is-at-least-32ch'))->decode($jwt);
    }

    public function test_throws_for_malformed_jwt(): void
    {
        $this->expectException(InvalidTokenException::class);

        (new Decoder($this->secret))->decode('not.a.valid.jwt.string');
    }
}
