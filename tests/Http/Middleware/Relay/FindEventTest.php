<?php

namespace Junction\Api\Test\Http\Middleware\Relay;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Event\Event;
use Junction\Api\Http\Middleware\Relay\FindEvent;
use Junction\Api\Relay\Command\QueryEvent;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindEventTest extends TestCase
{
    private function makeEvent(): Event
    {
        return new Event(['id' => 'event-uuid', 'name' => 'order.placed']);
    }

    public function test_dispatches_query_event_with_event_name_from_request(): void
    {
        $event = $this->makeEvent();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(QueryEvent $cmd) => $cmd->name === 'order.placed'))
            ->willReturn($event);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('event_name', '')->willReturn('order.placed');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new FindEvent($dispatcher))->process($request, $handler);
    }

    public function test_sets_event_model_as_request_attribute(): void
    {
        $event      = $this->makeEvent();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($event);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('event_name', '')->willReturn('order.placed');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(Event::class, $event)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new FindEvent($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_updated_request_to_handler(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeEvent());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('event_name', '')->willReturn('order.placed');
        $request->method('withAttribute')->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        (new FindEvent($dispatcher))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeEvent());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('event_name', '')->willReturn('order.placed');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new FindEvent($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
