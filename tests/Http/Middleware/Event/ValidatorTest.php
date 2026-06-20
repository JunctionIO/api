<?php

namespace Junction\Api\Test\Http\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Meritum\Validation\Validator;
use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\Event\Validator as EventValidator;

final class ValidatorTest extends TestCase
{
    public function test_rules(): void
    {
        $validator = new EventValidator($this->createMock(Validator::class));

        $this->assertSame(
            ['description' => ['nullable', 'required', 'string']],
            $validator->rules($this->createMock(ServerRequestInterface::class))
        );
    }
}
