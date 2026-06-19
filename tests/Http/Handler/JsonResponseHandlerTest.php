<?php

namespace Junction\Api\Test\Http\Handler;

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\FormatterInterface;
use Meritum\Serialization\Resource\ResourceInterface;
use Junction\Api\Http\Handler\JsonResponseHandler;

final class JsonResponseHandlerTest extends TestCase
{
    public function test_returns_json_response_with_default_status_200(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $envelope->method('jsonSerialize')->willReturn(['data' => []]);

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->method('format')->with($resource)->willReturn($envelope);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->willReturnCallback(fn(string $key, mixed $default = null) => match ($key) {
                'status'              => $default,
                ResourceInterface::class => $resource,
                default              => null,
            });

        $response = (new JsonResponseHandler($formatter))->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_returns_json_response_with_custom_status(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $envelope->method('jsonSerialize')->willReturn([]);

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->method('format')->willReturn($envelope);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->willReturnCallback(fn(string $key, mixed $default = null) => match ($key) {
                'status'              => 201,
                ResourceInterface::class => $resource,
                default              => null,
            });

        $response = (new JsonResponseHandler($formatter))->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_assert_fails_when_resource_not_set(): void
    {
        $formatter = $this->createMock(FormatterInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->willReturnCallback(fn(string $key, mixed $default = null) => match ($key) {
                'status'              => 200,
                ResourceInterface::class => null,
                default              => null,
            });

        $this->expectException(\AssertionError::class);

        (new JsonResponseHandler($formatter))->handle($request);
    }
}
