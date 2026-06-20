<?php

namespace Junction\Api\Http\Middleware;

use Meritum\Validation\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\HttpExceptionHandler\ErrorEnvelope;

abstract class AbstractValidator implements MiddlewareInterface
{
    public function __construct(private readonly Validator $validator) {}

    /**
     * @return array<string, array<int|string, mixed>>
     */
    abstract protected function rules(ServerRequestInterface $request): array;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var array<string, mixed> $input */
        $input = $request->getParsedBody() ?? [];

        $result = $this->validator->validate($this->rules($request), $input);

        if (false === $result->passed()) {
            $errors = [];

            foreach ($result->getErrors() as $attribute => $messages) {
                foreach ($messages as $message) {
                    $errors[] = ['field' => $attribute, 'message' => $message];
                }
            }

            $envelope = new ErrorEnvelope('VALIDATION_ERROR', 422, 'Validation Error')->withErrors($errors);

            return new JsonResponse($envelope, 422);
        }

        return $handler->handle($request);
    }
}
