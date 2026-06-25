<?php

namespace Junction\Api\Test\Http\Middleware\Destination;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\Destination\Command\Create as CreateCommand;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\Destination\Create;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateTest extends TestCase
{
    private function makeType(): DestinationType
    {
        return new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [],
        ]);
    }

    private function makeModel(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => ['url' => 'https://example.com/webhook'],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    public function test_dispatches_create_command_with_body_fields(): void
    {
        $type  = $this->makeType();
        $model = $this->makeModel();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CreateCommand $cmd) use ($type) {
                return $cmd->name        === 'My Webhook'
                    && $cmd->description === 'A description'
                    && $cmd->config      === ['url' => 'https://example.com/webhook']
                    && $cmd->status      === 'active'
                    && $cmd->events      === [['name' => 'order.placed']]
                    && $cmd->type        === $type;
            }))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'name'                => 'My Webhook',
            'description'         => 'A description',
            'destination_type_id' => 'type-uuid',
            'config'              => ['url' => 'https://example.com/webhook'],
            'status'              => 'active',
            'events'              => [['name' => 'order.placed']],
        ]);
        $request->method('getAttribute')->with(DestinationType::class)->willReturn($type);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Create($dispatcher))->process($request, $handler);
    }

    public function test_uses_null_description_when_absent_from_body(): void
    {
        $type  = $this->makeType();
        $model = $this->makeModel();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(CreateCommand $cmd) => null === $cmd->description))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'name'   => 'My Webhook',
            'config' => [],
            'status' => 'active',
            'events' => [],
        ]);
        $request->method('getAttribute')->with(DestinationType::class)->willReturn($type);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Create($dispatcher))->process($request, $handler);
    }

    public function test_sets_model_as_data_attribute_on_request(): void
    {
        $type       = $this->makeType();
        $model      = $this->makeModel();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'name'   => 'My Webhook',
            'config' => [],
            'status' => 'active',
            'events' => [],
        ]);
        $request->method('getAttribute')->with(DestinationType::class)->willReturn($type);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Create($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_returns_handler_response(): void
    {
        $type     = $this->makeType();
        $model    = $this->makeModel();
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'name'   => 'My Webhook',
            'config' => [],
            'status' => 'active',
            'events' => [],
        ]);
        $request->method('getAttribute')->with(DestinationType::class)->willReturn($type);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Create($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
