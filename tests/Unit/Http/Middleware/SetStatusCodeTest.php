<?php

namespace Junction\Api\Test\Unit\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Http\Middleware\SetStatusCode;

final class SetStatusCodeTest extends TestCase
{
    public function test_sets_status_attribute_on_request(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('status', 201)
            ->willReturn($newRequest);

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new SetStatusCode(201))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_configured_status_through(): void
    {
        $captured = null;
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withAttribute')
            ->willReturnCallback(function (string $key, mixed $value) use ($newRequest, &$captured) {
                $captured = $value;
                return $newRequest;
            });

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new SetStatusCode(204))->process($request, $handler);

        $this->assertSame(204, $captured);
    }
}
