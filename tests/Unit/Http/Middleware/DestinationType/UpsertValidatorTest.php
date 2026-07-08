<?php

namespace Junction\Api\Test\Unit\Http\Middleware\DestinationType;

use Junction\Api\Http\Middleware\DestinationType\UpsertValidator;
use Meritum\Validation\Validator as ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class UpsertValidatorTest extends TestCase
{
    private function rules(): array
    {
        return (new UpsertValidator($this->createMock(ValidationService::class)))
            ->rules($this->createMock(ServerRequestInterface::class));
    }

    public function test_name_rule(): void
    {
        $this->assertSame(['required', 'string'], $this->rules()['name']);
    }

    public function test_description_rule(): void
    {
        $this->assertSame(['nullable', 'string'], $this->rules()['description']);
    }

    public function test_queue_rule(): void
    {
        $this->assertSame(
            ['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']],
            $this->rules()['queue']
        );
    }

    public function test_config_schema_rule(): void
    {
        $this->assertSame(['required', 'config_schema'], $this->rules()['config_schema']);
    }

    public function test_only_four_rules(): void
    {
        $this->assertCount(4, $this->rules());
    }
}
