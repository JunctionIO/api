<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Destination;

use Junction\Api\Destination\Destination;
use Junction\Api\Destination\DestinationRepositoryInterface;
use Junction\Api\Http\Middleware\Destination\FindForUpdate;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindForUpdateTest extends TestCase
{
    private function makeDestination(): Destination
    {
        return new Destination([
            'id'                  => 'dest-uuid',
            'name'                => 'My Webhook',
            'destination_type_id' => 'type-uuid',
            'config'              => [],
            'status'              => 'active',
            'created_at'          => '2026-06-23 10:00:00',
            'updated_at'          => '2026-06-23 10:00:00',
        ]);
    }

    public function test_calls_find_or_fail_with_route_id(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('dest-uuid')
            ->willReturn($dest);

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new FindForUpdate($repo))->process($request, $handler);
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');

        $this->expectException(ModelNotFoundException::class);

        (new FindForUpdate($repo))->process($request, $this->createMock(RequestHandlerInterface::class));
    }

    public function test_sets_destination_model_as_attribute(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(Destination::class, $dest)
            ->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new FindForUpdate($repo))->process($request, $handler);
    }

    public function test_injects_destination_type_id_into_body_when_config_is_present(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);

        $body = ['config' => ['url' => 'https://example.com']];

        $requestWithBody = $this->createMock(ServerRequestInterface::class);
        $response        = $this->createMock(ResponseInterface::class);

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn($body);
        $requestWithModel->expects($this->once())
            ->method('withParsedBody')
            ->with($body + ['destination_type_id' => 'type-uuid'])
            ->willReturn($requestWithBody);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($requestWithBody)->willReturn($response);

        (new FindForUpdate($repo))->process($request, $handler);
    }

    public function test_does_not_modify_parsed_body_when_config_is_absent(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn(['name' => 'New Name']);
        $requestWithModel->expects($this->never())->method('withParsedBody');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($requestWithModel)->willReturn($this->createMock(ResponseInterface::class));

        (new FindForUpdate($repo))->process($request, $handler);
    }

    public function test_does_not_inject_destination_type_id_when_config_is_null(): void
    {
        $dest = $this->makeDestination();

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($dest);

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn(['config' => null]);
        $requestWithModel->expects($this->never())->method('withParsedBody');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new FindForUpdate($repo))->process($request, $handler);
    }

    public function test_returns_handler_response(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $repo = $this->createMock(DestinationRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($this->makeDestination());

        $requestWithModel = $this->createMock(ServerRequestInterface::class);
        $requestWithModel->method('getParsedBody')->willReturn([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('id', '')->willReturn('dest-uuid');
        $request->method('withAttribute')->willReturn($requestWithModel);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = (new FindForUpdate($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
