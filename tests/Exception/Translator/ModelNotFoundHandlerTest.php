<?php

namespace Junction\Api\Test\Exception\Translator;

use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\Http\Exception\NotFoundHttpException;
use Meritum\HttpExceptionHandler\HttpDomainException;
use Junction\Api\Exception\Translator\ModelNotFoundHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ModelNotFoundHandlerTest extends TestCase
{
    public function test_matches_model_not_found_exception(): void
    {
        $handler = new ModelNotFoundHandler($this->createMock(ServerRequestInterface::class));

        $this->assertTrue($handler->matches(new ModelNotFoundException()));
    }

    public function test_does_not_match_other_exceptions(): void
    {
        $handler = new ModelNotFoundHandler($this->createMock(ServerRequestInterface::class));

        $this->assertFalse($handler->matches(new RuntimeException()));
    }

    public function test_handle_returns_http_domain_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn(new \Laminas\Diactoros\Uri('/v0/events/uuid'));

        $handler = new ModelNotFoundHandler($request);
        $result  = $handler->handle(new ModelNotFoundException('Event not found.'));

        $this->assertInstanceOf(HttpDomainException::class, $result);
    }

    public function test_handle_wraps_not_found_http_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn(new \Laminas\Diactoros\Uri('/v0/events/uuid'));

        $handler  = new ModelNotFoundHandler($request);
        $original = new ModelNotFoundException('Event not found.');
        $result   = $handler->handle($original);

        $previous = $result->getPrevious();

        $this->assertInstanceOf(NotFoundHttpException::class, $previous);
        $this->assertSame(404, $previous->getCode());
        $this->assertSame('Event not found.', $previous->getMessage());
    }

    public function test_handle_chains_original_exception(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn(new \Laminas\Diactoros\Uri('/v0/events/uuid'));

        $handler  = new ModelNotFoundHandler($request);
        $original = new ModelNotFoundException('not found');
        $result   = $handler->handle($original);

        $this->assertSame($original, $result->getPrevious()?->getPrevious());
    }

    public function test_priority_returns_five(): void
    {
        $handler = new ModelNotFoundHandler($this->createMock(ServerRequestInterface::class));

        $this->assertSame(5, $handler->priority());
    }
}
