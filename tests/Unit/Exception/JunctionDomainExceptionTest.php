<?php

namespace Junction\Api\Test\Unit\Exception;

use Junction\Api\Exception\JunctionDomainException;
use Meritum\StructuredLogging\Severity;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JunctionDomainExceptionTest extends TestCase
{
    private RuntimeException $original;
    private JunctionDomainException $exception;

    protected function setUp(): void
    {
        $this->original  = new RuntimeException('Something went wrong', 42);
        $this->exception = new JunctionDomainException($this->original);
    }

    public function test_message_is_generic(): void
    {
        $this->assertSame('The application encountered an unknown error', $this->exception->getMessage());
    }

    public function test_error_code_is_junction_0000(): void
    {
        $this->assertSame('JUNCTION_0000', $this->exception->getErrorCode());
    }

    public function test_severity_is_error(): void
    {
        $this->assertSame(Severity::Error, $this->exception->severity);
    }

    public function test_is_not_retryable(): void
    {
        $this->assertFalse($this->exception->retryable);
    }

    public function test_chains_original_exception(): void
    {
        $this->assertSame($this->original, $this->exception->getPrevious());
    }

    public function test_context_contains_original_class(): void
    {
        $this->assertSame(RuntimeException::class, $this->exception->context['original_class']);
    }

    public function test_context_contains_original_message(): void
    {
        $this->assertSame('Something went wrong', $this->exception->context['original_message']);
    }

    public function test_context_contains_original_code(): void
    {
        $this->assertSame(42, $this->exception->context['original_code']);
    }

    public function test_context_contains_original_file(): void
    {
        $this->assertSame($this->original->getFile(), $this->exception->context['original_file']);
    }

    public function test_context_contains_original_line(): void
    {
        $this->assertSame($this->original->getLine(), $this->exception->context['original_line']);
    }
}
