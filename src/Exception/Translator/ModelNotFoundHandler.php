<?php

namespace Junction\Api\Exception\Translator;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Meritum\StructuredLogging\TranslationHandler;
use Meritum\Http\Exception\NotFoundHttpException;
use Meritum\HttpExceptionHandler\HttpDomainException;
use Meritum\Database\Exception\ModelNotFoundException;
use Meritum\StructuredLogging\Exception\DomainException;

final class ModelNotFoundHandler implements TranslationHandler
{
    public function __construct(private readonly ServerRequestInterface $request) {}

    public function matches(Throwable $exception): bool
    {
        return $exception instanceof ModelNotFoundException;
    }

    public function handle(Throwable $exception): DomainException
    {
        $e = new NotFoundHttpException($this->request, $exception->getMessage(), $exception);

        return new HttpDomainException($e);
    }

    public function priority(): int
    {
        return 0;
    }
}
