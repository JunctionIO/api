<?php

namespace Junction\Api\Exception\Translator;

use Throwable;
use Meritum\StructuredLogging\TranslationHandler;
use Junction\Api\Exception\JunctionDomainException;
use Meritum\StructuredLogging\Exception\DomainException;

final class JunctionDomainHandler implements TranslationHandler
{
    public function matches(Throwable $exception): bool
    {
        return true;
    }

    public function handle(Throwable $exception): DomainException
    {
        return new JunctionDomainException($exception);
    }

    public function priority(): int
    {
        return 0;
    }
}
