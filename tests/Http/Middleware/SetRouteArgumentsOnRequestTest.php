<?php

namespace Junction\Api\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Http\Routing\RouteInterface;
use Junction\Api\Http\Middleware\SetRouteArgumentsOnRequest;

final class SetRouteArgumentsOnRequestTest extends TestCase
{
    public function test_sets_route_arguments_as_request_attributes(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getArguments')->willReturn(['id' => 'uuid-123', 'type' => 'http']);

        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(RouteInterface::class)->willReturn($route);

        $requestWithId = $this->createMock(ServerRequestInterface::class);
        $requestWithId->method('withAttribute')->with('type', 'http')->willReturn(
            $requestWithType = $this->createMock(ServerRequestInterface::class)
        );

        $request->expects($this->once())
            ->method('withAttribute')
            ->with('id', 'uuid-123')
            ->willReturn($requestWithId);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($requestWithType)
            ->willReturn($response);

        $result = (new SetRouteArgumentsOnRequest())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_request_unmodified_when_no_arguments(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getArguments')->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(RouteInterface::class)->willReturn($route);
        $request->expects($this->never())->method('withAttribute');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        (new SetRouteArgumentsOnRequest())->process($request, $handler);
    }

    public function test_assert_fails_when_route_not_set(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(RouteInterface::class)->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(\AssertionError::class);

        (new SetRouteArgumentsOnRequest())->process($request, $handler);
    }
}
