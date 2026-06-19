<?php

namespace Junction\Api\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Support\CursorParams;
use Junction\Api\Http\Middleware\ParsePaginationQuery;

final class ParsePaginationQueryTest extends TestCase
{
    public function test_sets_cursor_params_attribute_on_request(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['limit' => '50', 'cursor' => 'tok']);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(CursorParams::class, $this->isInstanceOf(CursorParams::class))
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new ParsePaginationQuery())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_parses_limit_and_cursor_from_query(): void
    {
        $captured = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['limit' => '75', 'cursor' => 'abc123']);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$captured) {
                $captured = $value;
                return $newRequest;
            });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParsePaginationQuery())->process($request, $handler);

        $this->assertInstanceOf(CursorParams::class, $captured);
        $this->assertSame(75, $captured->limit);
        $this->assertSame('abc123', $captured->cursor);
    }

    public function test_applies_default_limit_when_query_is_empty(): void
    {
        $captured = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([]);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$captured) {
                $captured = $value;
                return $newRequest;
            });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParsePaginationQuery())->process($request, $handler);

        $this->assertInstanceOf(CursorParams::class, $captured);
        $this->assertSame(25, $captured->limit);
        $this->assertNull($captured->cursor);
    }

    public function test_respects_custom_default_and_max(): void
    {
        $captured = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['limit' => '200']);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$captured) {
                $captured = $value;
                return $newRequest;
            });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParsePaginationQuery(10, 50))->process($request, $handler);

        $this->assertInstanceOf(CursorParams::class, $captured);
        $this->assertSame(50, $captured->limit);
    }
}
