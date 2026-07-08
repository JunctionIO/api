<?php

namespace Junction\Api\Test\Unit\Http\Middleware\EventLog;

use Junction\Api\Event\Event;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Http\Middleware\EventLog\ParseEventFilter;
use Meritum\Database\Support\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ParseEventFilterTest extends TestCase
{
    public function test_passes_through_when_no_filter(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([]);
        $request->expects($this->never())->method('withAttribute');

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->never())->method('getByName');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new ParseEventFilter($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_sets_event_ids_when_events_match(): void
    {
        $event      = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $collection = new Collection(['event-uuid' => $event]);
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['filter' => ['event' => ['test.event']]]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('event_ids', ['event-uuid'])
            ->willReturn($newRequest);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn($collection);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        (new ParseEventFilter($repo))->process($request, $handler);
    }

    public function test_sets_empty_array_when_no_events_match(): void
    {
        $newRequest = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['filter' => ['event' => ['nonexistent.event']]]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('event_ids', [])
            ->willReturn($newRequest);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('getByName')->willReturn(new Collection([]));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParseEventFilter($repo))->process($request, $handler);
    }

    public function test_wraps_single_string_value_in_array(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['filter' => ['event' => 'test.event']]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getByName')
            ->with(['test.event'], ['id'])
            ->willReturn(new Collection([]));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParseEventFilter($repo))->process($request, $handler);
    }

    public function test_passes_multiple_event_names_to_repository(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['filter' => ['event' => ['event.one', 'event.two']]]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getByName')
            ->with(['event.one', 'event.two'], ['id'])
            ->willReturn(new Collection([]));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new ParseEventFilter($repo))->process($request, $handler);
    }
}
