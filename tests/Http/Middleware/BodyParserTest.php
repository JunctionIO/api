<?php

namespace Junction\Api\Test\Http\Middleware;

use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Exception\BadRequestHttpException;
use Junction\Api\Http\Middleware\BodyParser;

final class BodyParserTest extends TestCase
{
    private function streamFrom(string $content): StreamInterface
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    public function test_delegates_to_handler_without_parsing_for_get_request(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->expects($this->never())->method('getBody');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new BodyParser())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_parses_json_body_and_sets_parsed_body_on_post(): void
    {
        $parsedRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getBody')->willReturn($this->streamFrom('{"name":"junction"}'));
        $request->expects($this->once())
            ->method('withParsedBody')
            ->with(['name' => 'junction'])
            ->willReturn($parsedRequest);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($parsedRequest)->willReturn($response);

        $result = (new BodyParser())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_parses_json_body_on_put(): void
    {
        $parsedRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('PUT');
        $request->method('getBody')->willReturn($this->streamFrom('{"name":"junction"}'));
        $request->method('withParsedBody')->willReturn($parsedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($parsedRequest)->willReturn($this->createMock(ResponseInterface::class));

        (new BodyParser())->process($request, $handler);
    }

    public function test_parses_json_body_on_patch(): void
    {
        $parsedRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('PATCH');
        $request->method('getBody')->willReturn($this->streamFrom('{"name":"junction"}'));
        $request->method('withParsedBody')->willReturn($parsedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($parsedRequest)->willReturn($this->createMock(ResponseInterface::class));

        (new BodyParser())->process($request, $handler);
    }

    public function test_throws_when_body_is_invalid_json(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getBody')->willReturn($this->streamFrom('not valid json'));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(BadRequestHttpException::class);

        (new BodyParser())->process($request, $handler);
    }

    public function test_throws_when_body_is_empty(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getBody')->willReturn($this->streamFrom(''));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(BadRequestHttpException::class);

        (new BodyParser())->process($request, $handler);
    }

    public function test_throws_when_body_is_not_a_json_object(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getBody')->willReturn($this->streamFrom('"just a string"'));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(BadRequestHttpException::class);

        (new BodyParser())->process($request, $handler);
    }
}
