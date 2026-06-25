<?php

namespace Junction\Api\Test\Http\Middleware\Destination;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\DestinationType\DestinationTypeRepositoryInterface;
use Junction\Api\Http\Middleware\Destination\FindDestinationType;
use Meritum\Database\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FindDestinationTypeTest extends TestCase
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

    public function test_sets_destination_type_attribute_when_id_is_present(): void
    {
        $type       = $this->makeType();
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['destination_type_id' => 'type-uuid']);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with(DestinationType::class, $type)
            ->willReturn($newRequest);

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findOrFail')->willReturn($type);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($newRequest)->willReturn($response);

        $result = (new FindDestinationType($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_destination_type_id_to_repository(): void
    {
        $type = $this->makeType();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['destination_type_id' => 'type-uuid']);
        $request->method('withAttribute')->willReturn($this->createMock(ServerRequestInterface::class));

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findOrFail')
            ->with('type-uuid')
            ->willReturn($type);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        (new FindDestinationType($repo))->process($request, $handler);
    }

    public function test_passes_through_without_setting_attribute_when_id_is_null(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['destination_type_id' => null]);
        $request->expects($this->never())->method('withAttribute');

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->never())->method('findOrFail');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new FindDestinationType($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_passes_through_without_setting_attribute_when_id_is_absent(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);
        $request->expects($this->never())->method('withAttribute');

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->expects($this->never())->method('findOrFail');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $result = (new FindDestinationType($repo))->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function test_propagates_model_not_found_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['destination_type_id' => 'type-uuid']);

        $repo = $this->createMock(DestinationTypeRepositoryInterface::class);
        $repo->method('findOrFail')->willThrowException(new ModelNotFoundException());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->expectException(ModelNotFoundException::class);

        (new FindDestinationType($repo))->process($request, $handler);
    }
}
