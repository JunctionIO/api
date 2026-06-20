<?php

namespace Junction\Api\Test\Http\Middleware;

use Meritum\Http\Exception\NotFoundHttpException;
use Meritum\Validation\Rule\Uuid;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Junction\Api\Http\Middleware\ValidateUuidId;

final class ValidateUuidIdTest extends TestCase
{
    public function test_delegates_to_handler_when_id_is_a_valid_uuid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new ValidateUuidId(new Uuid()))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_throws_not_found_when_id_is_not_a_valid_uuid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('not-a-uuid');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(NotFoundHttpException::class);

        (new ValidateUuidId(new Uuid()))->process($request, $handler);
    }

    public function test_throws_not_found_when_id_is_empty(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(NotFoundHttpException::class);

        (new ValidateUuidId(new Uuid()))->process($request, $handler);
    }

    public function test_throws_not_found_when_id_is_a_plain_string(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('foo');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(NotFoundHttpException::class);

        (new ValidateUuidId(new Uuid()))->process($request, $handler);
    }
}
