<?php

namespace Junction\Api\Test\Http\Middleware\DestinationLog;

use Junction\Api\Http\Middleware\DestinationLog\UpdateValidator;
use Meritum\Validation\Validator as ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateValidatorTest extends TestCase
{
    private function rules(): array
    {
        return (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->createMock(ServerRequestInterface::class));
    }

    public function test_log_id_rule(): void
    {
        $this->assertSame(['required', 'string'], $this->rules()['log_id']);
    }

    public function test_status_rule(): void
    {
        $this->assertSame(
            ['required', 'string', 'in' => ['dispatched', 'errored']],
            $this->rules()['status']
        );
    }

    public function test_attempted_at_rule(): void
    {
        $this->assertSame(
            ['required', 'string', 'dateFormat' => ['Y-m-d H:i:s']],
            $this->rules()['attempted_at']
        );
    }

    public function test_error_rule(): void
    {
        $this->assertSame(['nullable', 'string'], $this->rules()['error']);
    }

    public function test_only_four_rules(): void
    {
        $this->assertCount(4, $this->rules());
    }
}
