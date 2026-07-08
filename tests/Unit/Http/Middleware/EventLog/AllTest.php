<?php

namespace Junction\Api\Test\Unit\Http\Middleware\EventLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\EventLog\Command\QueryAll;
use Junction\Api\Http\Middleware\EventLog\All;
use Junction\Api\Support\CursorParams;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AllTest extends TestCase
{
    public function test_dispatches_query_all_with_cursor_params(): void
    {
        $params   = new CursorParams(['limit' => '25']);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [CursorParams::class, null, $params],
            ['event_ids', null, null],
        ]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (QueryAll $cmd) => $cmd->limit === 25 && $cmd->cursor === null && $cmd->eventIds === null))
            ->willReturn(new CursorPaginator(new Collection([]), null, null, 25));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        (new All($bus))->process($request, $handler);
    }

    public function test_passes_cursor_token_to_command(): void
    {
        $params = new CursorParams(['limit' => '25', 'cursor' => 'tok123']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [CursorParams::class, null, $params],
            ['event_ids', null, null],
        ]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (QueryAll $cmd) => $cmd->cursor === 'tok123'))
            ->willReturn(new CursorPaginator(new Collection([]), null, null, 25));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new All($bus))->process($request, $handler);
    }

    public function test_passes_event_ids_to_command(): void
    {
        $params = new CursorParams(['limit' => '25']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [CursorParams::class, null, $params],
            ['event_ids', null, ['uuid-1', 'uuid-2']],
        ]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (QueryAll $cmd) => $cmd->eventIds === ['uuid-1', 'uuid-2']))
            ->willReturn(new CursorPaginator(new Collection([]), null, null, 25));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new All($bus))->process($request, $handler);
    }

    public function test_sets_result_as_data_attribute_and_delegates(): void
    {
        $paginator   = new CursorPaginator(new Collection([]), null, null, 25);
        $params      = new CursorParams(['limit' => '25']);
        $newRequest  = $this->createMock(ServerRequestInterface::class);
        $response    = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            [CursorParams::class, null, $params],
            ['event_ids', null, null],
        ]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $paginator)
            ->willReturn($newRequest);

        $bus = $this->createMock(DispatcherInterface::class);
        $bus->method('dispatch')->willReturn($paginator);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new All($bus))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_assert_fails_when_cursor_params_not_set(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn(null);

        $this->expectException(\AssertionError::class);

        (new All($this->createMock(DispatcherInterface::class)))->process(
            $request,
            $this->createMock(RequestHandlerInterface::class)
        );
    }
}
