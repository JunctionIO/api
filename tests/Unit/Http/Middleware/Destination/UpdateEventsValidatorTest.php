<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Destination;

use Junction\Api\Http\Middleware\Destination\UpdateEventsValidator;
use Meritum\Validation\Validator as ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateEventsValidatorTest extends TestCase
{
    public function test_events_rule(): void
    {
        $rules = (new UpdateEventsValidator($this->createMock(ValidationService::class)))
            ->rules($this->createMock(ServerRequestInterface::class));

        $this->assertSame(['required', 'array'], $rules['events']);
    }

    public function test_events_name_rule(): void
    {
        $rules = (new UpdateEventsValidator($this->createMock(ValidationService::class)))
            ->rules($this->createMock(ServerRequestInterface::class));

        $this->assertSame(['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']], $rules['events.*.name']);
    }

    public function test_only_two_rules(): void
    {
        $rules = (new UpdateEventsValidator($this->createMock(ValidationService::class)))
            ->rules($this->createMock(ServerRequestInterface::class));

        $this->assertCount(2, $rules);
    }
}
