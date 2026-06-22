<?php

namespace Junction\Api\Test\Http\Middleware\EventLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Event\Event;
use Junction\Api\EventLog\EventLog;
use Junction\Api\EventLog\Command\QueryFind;
use Junction\Api\Http\Middleware\EventLog\Find;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindTest extends TestCase
{
    public function test_sets_model_as_data_attribute_when_found(): void
    {
        $event      = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $log        = new EventLog(['id' => 'log-uuid', 'event_id' => 'event-uuid']);
        $log->setEvent($event);
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('log-uuid');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $log)
            ->willReturn($newRequest);

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->method('dispatch')->willReturn($log);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Find($bus))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('log-uuid');

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->method('dispatch')->willThrowException(new ModelNotFoundException());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(ModelNotFoundException::class);

        (new Find($bus))->process($request, $handler);
    }

    public function test_dispatches_query_find_with_id(): void
    {
        $event = new Event(['id' => 'event-uuid', 'name' => 'test.event']);
        $log   = new EventLog(['id' => 'log-uuid', 'event_id' => 'event-uuid']);
        $log->setEvent($event);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('log-uuid');
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (QueryFind $cmd) => $cmd->id === 'log-uuid'))
            ->willReturn($log);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Find($bus))->process($request, $handler);
    }
}
