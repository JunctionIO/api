<?php

namespace Junction\Api\Test\Unit\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\StructuredLogging\CorrelationId;
use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\Correlation;

final class CorrelationTest extends TestCase
{
    public function test_sets_correlation_id_from_request_header(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $id = new CorrelationId();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Correlation-ID')->willReturn($uuid);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->with('X-Correlation-ID', $uuid)->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new Correlation($id))->process($request, $handler);

        $this->assertSame($uuid, $id->uuid);
    }

    public function test_ignores_invalid_correlation_id_header(): void
    {
        $id = new CorrelationId();
        $generated = $id->uuid;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Correlation-ID')->willReturn('not-a-uuid');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new Correlation($id))->process($request, $handler);

        $this->assertSame($generated, $id->uuid);
    }

    public function test_generates_correlation_id_when_header_absent(): void
    {
        $id = new CorrelationId();
        $generated = $id->uuid;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Correlation-ID')->willReturn('');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new Correlation($id))->process($request, $handler);

        $this->assertSame($generated, $id->uuid);
    }

    public function test_sets_correlation_id_on_response(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $id = new CorrelationId();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Correlation-ID')->willReturn($uuid);

        $response = $this->createMock(ResponseInterface::class);
        $finalResponse = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('withHeader')
            ->with('X-Correlation-ID', $uuid)
            ->willReturn($finalResponse);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Correlation($id))->process($request, $handler);

        $this->assertSame($finalResponse, $result);
    }
}
