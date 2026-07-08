<?php

namespace Junction\Api\Test\Unit\Http\Middleware;

use Junction\Api\ApiToken\DecoderInterface;
use Junction\Api\ApiToken\InvalidTokenException;
use Junction\Api\ApiToken\Token;
use Junction\Api\Exception\UnauthorizedHttpException;
use Junction\Api\Http\Middleware\ValidateApiToken;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final class ValidateApiTokenTest extends TestCase
{
    private function makeRequest(string $headerValue): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Token')->willReturn($headerValue);

        return $request;
    }

    private function makeDecoder(Token $token): DecoderInterface
    {
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willReturn($token);

        return $decoder;
    }

    public function test_throws_unauthorized_when_header_is_empty(): void
    {
        $decoder = $this->createMock(DecoderInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(UnauthorizedHttpException::class);

        (new ValidateApiToken($decoder, 'relay'))->process($this->makeRequest(''), $handler);
    }

    public function test_throws_unauthorized_when_token_type_does_not_match(): void
    {
        $token   = new Token('my-id', 'management', 1700000000);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(UnauthorizedHttpException::class);

        (new ValidateApiToken($this->makeDecoder($token), 'relay'))->process($this->makeRequest('some.jwt.token'), $handler);
    }

    public function test_propagates_invalid_token_exception(): void
    {
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willThrowException(new InvalidTokenException(new RuntimeException('bad')));
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(InvalidTokenException::class);

        (new ValidateApiToken($decoder, 'relay'))->process($this->makeRequest('bad.jwt.token'), $handler);
    }

    public function test_sets_token_as_request_attribute(): void
    {
        $token          = new Token('my-id', 'relay', 1700000000);
        $request        = $this->makeRequest('some.jwt.token');
        $updatedRequest = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())
            ->method('withAttribute')
            ->with(Token::class, $token)
            ->willReturn($updatedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ValidateApiToken($this->makeDecoder($token), 'relay'))->process($request, $handler);
    }

    public function test_passes_updated_request_to_handler(): void
    {
        $token          = new Token('my-id', 'relay', 1700000000);
        $request        = $this->makeRequest('some.jwt.token');
        $updatedRequest = $this->createMock(ServerRequestInterface::class);

        $request->method('withAttribute')->willReturn($updatedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($updatedRequest)
            ->willReturn($this->createMock(ResponseInterface::class));

        (new ValidateApiToken($this->makeDecoder($token), 'relay'))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $token    = new Token('my-id', 'relay', 1700000000);
        $request  = $this->makeRequest('some.jwt.token');
        $response = $this->createMock(ResponseInterface::class);

        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new ValidateApiToken($this->makeDecoder($token), 'relay'))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
