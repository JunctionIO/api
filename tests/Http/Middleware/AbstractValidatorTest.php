<?php

namespace Junction\Api\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Meritum\Validation\Validator;
use Meritum\Validation\ValidationResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Junction\Api\Http\Middleware\AbstractValidator;

final class AbstractValidatorTest extends TestCase
{
    private function makeMiddleware(Validator $validator): AbstractValidator
    {
        return new class($validator) extends AbstractValidator {
            public function rules(ServerRequestInterface $request): array
            {
                return [];
            }
        };
    }

    private function passingResult(): ValidationResult
    {
        $result = $this->createMock(ValidationResult::class);
        $result->method('passed')->willReturn(true);

        return $result;
    }

    private function failingResult(array $errors = []): ValidationResult
    {
        $result = $this->createMock(ValidationResult::class);
        $result->method('passed')->willReturn(false);
        $result->method('getErrors')->willReturn($errors);

        return $result;
    }

    public function test_delegates_to_handler_when_validation_passes(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->passingResult());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $actual = $this->makeMiddleware($validator)->process($request, $handler);

        $this->assertSame($response, $actual);
    }

    public function test_does_not_delegate_to_handler_when_validation_fails(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $this->makeMiddleware($validator)->process($request, $handler);
    }

    public function test_returns_422_when_validation_fails(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->makeMiddleware($validator)->process($request, $handler);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_error_envelope_structure(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->makeMiddleware($validator)->process($request, $handler);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame('VALIDATION_ERROR', $body['code']);
        $this->assertSame(422, $body['status']);
        $this->assertSame('Validation Error', $body['title']);
        $this->assertNull($body['detail']);
        $this->assertArrayNotHasKey('errors', $body);
    }

    public function test_maps_single_field_error_to_field_and_message(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult([
            'name' => ['The name field is required.'],
        ]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->makeMiddleware($validator)->process($request, $handler);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame([
            ['field' => 'name', 'message' => 'The name field is required.'],
        ], $body['errors']);
    }

    public function test_flattens_multiple_messages_for_same_field(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult([
            'email' => ['The email field is required.', 'The email must be a valid email address.'],
        ]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->makeMiddleware($validator)->process($request, $handler);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame([
            ['field' => 'email', 'message' => 'The email field is required.'],
            ['field' => 'email', 'message' => 'The email must be a valid email address.'],
        ], $body['errors']);
    }

    public function test_maps_errors_across_multiple_fields(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->method('validate')->willReturn($this->failingResult([
            'name'  => ['The name field is required.'],
            'email' => ['The email field is required.'],
        ]));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->makeMiddleware($validator)->process($request, $handler);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame([
            ['field' => 'name',  'message' => 'The name field is required.'],
            ['field' => 'email', 'message' => 'The email field is required.'],
        ], $body['errors']);
    }

    public function test_null_body_passes_empty_array_to_validator(): void
    {
        $validator = $this->createMock(Validator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($this->anything(), [])
            ->willReturn($this->passingResult());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        $this->makeMiddleware($validator)->process($request, $handler);
    }
}
