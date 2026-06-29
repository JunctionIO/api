<?php

namespace Junction\Api\ApiToken;

interface DecoderInterface
{
    /**
     * @throws InvalidTokenException
     */
    public function decode(string $jwt): Token;
}
