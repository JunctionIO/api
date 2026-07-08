<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Relay;

use Junction\Api\Exception\BadRequestHttpException;
use Junction\Api\Http\Middleware\Relay\ValidateEvent;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ValidateEventTest extends TestCase
{
    public function test_passes_valid_event_name_to_handler(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('order.placed');
        $request->method('withAttribute')->with('event_name', 'order.placed')->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new ValidateEvent())->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_sets_event_name_attribute_on_request(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('user.signup');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('event_name', 'user.signup')
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ValidateEvent())->process($request, $handler);
    }

    public function test_throws_on_empty_header(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('');

        $this->expectException(BadRequestHttpException::class);

        (new ValidateEvent())->process($request, $this->createMock(RequestHandlerInterface::class));
    }

    public function test_throws_on_event_name_with_spaces(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('order placed');

        $this->expectException(BadRequestHttpException::class);

        (new ValidateEvent())->process($request, $this->createMock(RequestHandlerInterface::class));
    }

    public function test_throws_on_event_name_with_slashes(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('order/placed');

        $this->expectException(BadRequestHttpException::class);

        (new ValidateEvent())->process($request, $this->createMock(RequestHandlerInterface::class));
    }

    public function test_throws_on_event_name_with_special_characters(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('order@placed!');

        $this->expectException(BadRequestHttpException::class);

        (new ValidateEvent())->process($request, $this->createMock(RequestHandlerInterface::class));
    }

    public function test_accepts_alphanumeric_event_name(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $request  = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('orderPlaced123');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ValidateEvent())->process($request, $handler);

        $this->addToAssertionCount(1);
    }

    public function test_accepts_event_name_with_dots_dashes_and_underscores(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $request  = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')->with('X-Junction-Event')->willReturn('order.placed-v2_final');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new ValidateEvent())->process($request, $handler);

        $this->addToAssertionCount(1);
    }
}
