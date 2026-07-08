<?php

namespace Junction\Api\Test\Unit\ApiToken;

use Junction\Api\ApiToken\InvalidTokenException;
use Meritum\StructuredLogging\Severity;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InvalidTokenExceptionTest extends TestCase
{
    private RuntimeException $cause;
    private InvalidTokenException $exception;

    protected function setUp(): void
    {
        $this->cause     = new RuntimeException('Signature verification failed');
        $this->exception = new InvalidTokenException($this->cause);
    }

    public function test_message(): void
    {
        $this->assertSame('Invalid or malformed API token', $this->exception->getMessage());
    }

    public function test_wraps_previous_exception(): void
    {
        $this->assertSame($this->cause, $this->exception->getPrevious());
    }

    public function test_severity_is_warning(): void
    {
        $this->assertSame(Severity::Warning, $this->exception->severity);
    }

    public function test_is_not_retryable(): void
    {
        $this->assertFalse($this->exception->retryable);
    }

    public function test_error_code(): void
    {
        $this->assertSame('API_TOKEN_0000', $this->exception->getErrorCode());
    }

    public function test_context_includes_reason_from_cause(): void
    {
        $this->assertSame('Signature verification failed', $this->exception->context['reason']);
    }
}
