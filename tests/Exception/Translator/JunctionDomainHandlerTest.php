<?php

namespace Junction\Api\Test\Exception\Translator;

use Junction\Api\Exception\JunctionDomainException;
use Junction\Api\Exception\Translator\JunctionDomainHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JunctionDomainHandlerTest extends TestCase
{
    public function test_matches_any_throwable(): void
    {
        $this->assertTrue((new JunctionDomainHandler())->matches(new RuntimeException()));
    }

    public function test_handle_returns_junction_domain_exception(): void
    {
        $result = (new JunctionDomainHandler())->handle(new RuntimeException('fail'));

        $this->assertInstanceOf(JunctionDomainException::class, $result);
    }

    public function test_handle_chains_original_exception(): void
    {
        $original = new RuntimeException('fail');
        $result   = (new JunctionDomainHandler())->handle($original);

        $this->assertSame($original, $result->getPrevious());
    }

    public function test_priority_returns_zero(): void
    {
        $this->assertSame(0, (new JunctionDomainHandler())->priority());
    }
}
