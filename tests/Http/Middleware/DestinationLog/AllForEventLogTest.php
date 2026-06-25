<?php

namespace Junction\Api\Test\Http\Middleware\DestinationLog;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\DestinationLog\Command\QueryAllForEventLog;
use Junction\Api\Http\Middleware\DestinationLog\AllForEventLog;
use Junction\Api\Support\CursorParams;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AllForEventLogTest extends TestCase
{
    private function makePaginator(): CursorPaginator
    {
        return new CursorPaginator(new Collection([]), null, null, 25);
    }

    public function test_dispatches_command_with_id_and_cursor_params(): void
    {
        $params = new CursorParams(['limit' => '25']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            ['id', '', 'elog-uuid'],
            [CursorParams::class, null, $params],
        ]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(QueryAllForEventLog $cmd) => $cmd->eventLogId === 'elog-uuid'
                    && $cmd->limit === 25
                    && $cmd->cursor === null
            ))
            ->willReturn($this->makePaginator());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new AllForEventLog($dispatcher))->process($request, $handler);
    }

    public function test_passes_cursor_token_to_command(): void
    {
        $params = new CursorParams(['limit' => '25', 'cursor' => 'tok123']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            ['id', '', 'elog-uuid'],
            [CursorParams::class, null, $params],
        ]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(QueryAllForEventLog $cmd) => $cmd->cursor === 'tok123'))
            ->willReturn($this->makePaginator());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new AllForEventLog($dispatcher))->process($request, $handler);
    }

    public function test_sets_result_as_data_attribute_and_delegates(): void
    {
        $paginator  = $this->makePaginator();
        $params     = new CursorParams(['limit' => '25']);
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            ['id', '', 'elog-uuid'],
            [CursorParams::class, null, $params],
        ]);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $paginator)
            ->willReturn($newRequest);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($paginator);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new AllForEventLog($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_assert_fails_when_cursor_params_not_set(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnMap([
            ['id', '', 'elog-uuid'],
            [CursorParams::class, null, null],
        ]);

        $this->expectException(\AssertionError::class);

        (new AllForEventLog($this->createMock(DispatcherInterface::class)))->process(
            $request,
            $this->createMock(RequestHandlerInterface::class)
        );
    }
}
