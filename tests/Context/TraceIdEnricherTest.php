<?php

namespace Junction\Api\Test\Context;

use Junction\Api\Context\TraceIdEnricher;
use Junction\Api\Trace\TraceId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class TraceIdEnricherTest extends TestCase
{
    private function makeRequest(string $path): ServerRequestInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    public function test_adds_trace_id_on_relay_route(): void
    {
        $trace = new TraceId();
        $trace->set('550e8400-e29b-41d4-a716-446655440000');

        $result = (new TraceIdEnricher($trace, $this->makeRequest('/relay')))->enrich([]);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result['trace_id']);
    }

    public function test_returns_context_unchanged_on_non_relay_route(): void
    {
        $trace = new TraceId();
        $enricher = new TraceIdEnricher($trace, $this->makeRequest('/v0/destinations'));

        $result = $enricher->enrich(['foo' => 'bar']);

        $this->assertArrayNotHasKey('trace_id', $result);
        $this->assertSame('bar', $result['foo']);
    }

    public function test_does_not_overwrite_existing_trace_id(): void
    {
        $trace = new TraceId();
        $trace->set('550e8400-e29b-41d4-a716-446655440000');

        $result = (new TraceIdEnricher($trace, $this->makeRequest('/relay')))->enrich([
            'trace_id' => 'existing-id',
        ]);

        $this->assertSame('existing-id', $result['trace_id']);
    }

    public function test_preserves_existing_context_entries(): void
    {
        $trace = new TraceId();

        $result = (new TraceIdEnricher($trace, $this->makeRequest('/relay')))->enrich([
            'request_id' => 'abc-123',
        ]);

        $this->assertSame('abc-123', $result['request_id']);
        $this->assertArrayHasKey('trace_id', $result);
    }
}
