<?php

namespace Junction\Api\Test\Http\Middleware\Relay;

use Junction\Api\Http\Middleware\Relay\ResolveTraceId;
use Junction\Api\Trace\TraceId;
use Meritum\Validation\Rule\Uuid;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResolveTraceIdTest extends TestCase
{
    private string $validUuid = '550e8400-e29b-41d4-a716-446655440000';

    public function test_sets_trace_id_from_valid_header(): void
    {
        $traceId = new TraceId();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Trace-ID')->willReturn($this->validUuid);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);

        $this->assertSame($this->validUuid, $traceId->id);
    }

    public function test_preserves_existing_trace_id_when_header_is_empty(): void
    {
        $traceId   = new TraceId();
        $generated = $traceId->id;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Trace-ID')->willReturn('');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);

        $this->assertSame($generated, $traceId->id);
    }

    public function test_preserves_existing_trace_id_when_header_is_not_a_uuid(): void
    {
        $traceId   = new TraceId();
        $generated = $traceId->id;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Trace-ID')->willReturn('not-a-uuid');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);

        $this->assertSame($generated, $traceId->id);
    }

    public function test_sets_trace_id_attribute_on_request(): void
    {
        $traceId = new TraceId();
        $traceId->set($this->validUuid);

        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->willReturn('');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(TraceId::class, $traceId)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);
    }

    public function test_adds_trace_id_to_response_header(): void
    {
        $traceId = new TraceId();
        $traceId->set($this->validUuid);

        $finalResponse = $this->createMock(ResponseInterface::class);
        $response      = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('withHeader')
            ->with('X-Junction-Trace-ID', $this->validUuid)
            ->willReturn($finalResponse);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);

        $this->assertSame($finalResponse, $result);
    }

    public function test_passes_updated_request_to_handler(): void
    {
        $traceId    = new TraceId();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('withAttribute')->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        (new ResolveTraceId($traceId, new Uuid()))->process($request, $handler);
    }
}
