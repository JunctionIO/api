<?php

namespace Junction\Api\Test\Unit\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Exception\UnsupportedMediaTypeHttpException;
use Junction\Api\Http\Middleware\ValidateContentType;

final class ValidateContentTypeTest extends TestCase
{
    public function test_delegates_to_handler_for_get_request(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->expects($this->never())->method('getHeaderLine');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new ValidateContentType())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_delegates_to_handler_when_content_type_is_application_json(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($response);

        $result = (new ValidateContentType())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_accepts_content_type_with_charset_parameter(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json; charset=utf-8');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ValidateContentType())->process($request, $handler);
    }

    public function test_accepts_content_type_with_mixed_case(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('Application/JSON');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ValidateContentType())->process($request, $handler);
    }

    public function test_throws_when_content_type_is_wrong_on_post(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('text/plain');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        (new ValidateContentType())->process($request, $handler);
    }

    public function test_throws_when_content_type_is_wrong_on_put(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('PUT');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('text/plain');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        (new ValidateContentType())->process($request, $handler);
    }

    public function test_throws_when_content_type_is_wrong_on_patch(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('PATCH');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('text/plain');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        (new ValidateContentType())->process($request, $handler);
    }

    public function test_throws_when_content_type_is_absent_on_write_method(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        (new ValidateContentType())->process($request, $handler);
    }
}
