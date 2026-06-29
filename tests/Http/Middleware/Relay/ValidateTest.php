<?php

namespace Junction\Api\Test\Http\Middleware\Relay;

use Junction\Api\Http\Middleware\Relay\Validate;
use Meritum\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class ValidateTest extends TestCase
{
    public function test_payload_is_required(): void
    {
        $rules = (new Validate($this->createMock(Validator::class)))
            ->rules($this->createMock(ServerRequestInterface::class));

        $this->assertContains('required', $rules['payload']);
    }

    public function test_payload_must_be_array(): void
    {
        $rules = (new Validate($this->createMock(Validator::class)))
            ->rules($this->createMock(ServerRequestInterface::class));

        $this->assertContains('array', $rules['payload']);
    }
}
