<?php

namespace Junction\Api\Test\Http\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\Database\Support\Collection;
use Meritum\Database\Support\CursorPaginator;
use Junction\Api\Support\CursorParams;
use Junction\Api\Event\EventRepositoryInterface;
use Junction\Api\Http\Middleware\Event\All;

final class AllTest extends TestCase
{
    public function test_sets_cursor_paginator_as_data_attribute(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);
        $params = new CursorParams(['limit' => '25']);

        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(CursorParams::class)->willReturn($params);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $paginator)
            ->willReturn($newRequest);

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->method('all')->with(25, null)->willReturn($paginator);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new All($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_cursor_token_to_repository(): void
    {
        $paginator = new CursorPaginator(new Collection([]), null, null, 25);
        $params = new CursorParams(['limit' => '25', 'cursor' => 'tok123']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(CursorParams::class)->willReturn($params);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(EventRepositoryInterface::class);
        $repo->expects($this->once())->method('all')->with(25, 'tok123')->willReturn($paginator);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new All($repo))->process($request, $handler);
    }

    public function test_assert_fails_when_cursor_params_not_set(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with(CursorParams::class)->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(\AssertionError::class);

        (new All($this->createMock(EventRepositoryInterface::class)))->process($request, $handler);
    }
}
