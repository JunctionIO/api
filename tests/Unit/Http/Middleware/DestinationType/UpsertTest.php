<?php

namespace Junction\Api\Test\Unit\Http\Middleware\DestinationType;

use Georgeff\Bus\DispatcherInterface;
use Junction\Api\DestinationType\Command\Upsert as UpsertCommand;
use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\DestinationType\Upsert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UpsertTest extends TestCase
{
    private function makeModel(): DestinationType
    {
        return new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'webhook',
            'queue'         => 'junction.destinations.webhook',
            'description'   => 'My Webhook',
            'config_schema' => [],
            'created_at'    => '2026-06-25 10:00:00',
            'updated_at'    => '2026-06-25 10:00:00',
        ]);
    }

    private function makeBody(): array
    {
        return [
            'name'          => 'webhook',
            'queue'         => 'webhook',
            'description'   => 'My Webhook',
            'config_schema' => ['url' => ['required' => true, 'rules' => ['string', 'url']]],
        ];
    }

    public function test_dispatches_upsert_command_with_input_data(): void
    {
        $body  = $this->makeBody();
        $model = $this->makeModel();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(UpsertCommand $cmd) => $cmd->name === 'webhook'
                    && $cmd->queue === 'webhook'
                    && $cmd->description === 'My Webhook'
                    && $cmd->configSchema === $body['config_schema']
            ))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Upsert($dispatcher))->process($request, $handler);
    }

    public function test_dispatches_with_null_description_when_absent(): void
    {
        $model = $this->makeModel();

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn(UpsertCommand $cmd) => null === $cmd->description))
            ->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['name' => 'webhook', 'queue' => 'webhook', 'config_schema' => []]);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new Upsert($dispatcher))->process($request, $handler);
    }

    public function test_sets_result_as_data_attribute(): void
    {
        $model      = $this->makeModel();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($model);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($this->makeBody());
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('data', $model)
            ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new Upsert($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn($this->makeModel());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($this->makeBody());
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new Upsert($dispatcher))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
