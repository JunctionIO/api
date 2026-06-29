<?php

namespace Junction\Api\ApiToken;

use Throwable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class Decoder implements DecoderInterface
{
    public function __construct(
        private readonly string $secret,
        private readonly string $algo = 'HS256'
    ) {}

    public function decode(string $jwt): Token
    {
        try {
            $claims = JWT::decode($jwt, new Key($this->secret, $this->algo));

            if (!is_string($claims->id) || !is_string($claims->type) || !is_int($claims->iat)) {
                throw new \UnexpectedValueException('JWT is missing required claims');
            }
        } catch (Throwable $e) {
            throw new InvalidTokenException($e);
        }

        return new Token($claims->id, $claims->type, $claims->iat);
    }
}
