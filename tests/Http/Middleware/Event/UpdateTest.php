<?php

namespace Junction\Api\Test\Http\Middleware\Event;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Event\Command\Update as UpdateCommand;
use Junction\Api\Event\Event;
use Junction\Api\Http\Middleware\Event\Update;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UpdateTest extends TestCase
{
    public function test_dispatches_update_command_with_id_and_description(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $request->method('getParsedBody')->willReturn(['description' => 'New description']);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (UpdateCommand $cmd) {
                return $cmd->id === '550e8400-e29b-41d4-a716-446655440000'
                    && $cmd->description === 'New description';
            }))
            ->willReturn(new Event(['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'test.event']));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Update($dispatcher))->process($request, $handler);
    }

    public function test_dispatches_with_null_description(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $request->method('getParsedBody')->willReturn(['description' => null]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (UpdateCommand $cmd) => null === $cmd->description))
            ->willReturn(new Event(['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'test.event']));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Update($dispatcher))->process($request, $handler);
    }

    public function test_sets_model_as_data_attribute_and_delegates_to_handler(): void
    {
        $model = new Event(['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'test.event']);
        $updatedRequest = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $request->method('getParsedBody')->willReturn(['description' => 'New description']);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($updatedRequest);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($updatedRequest)->willReturn($response);

        $result = (new Update($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
