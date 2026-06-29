<?php

namespace Junction\Api\ApiToken;

use Throwable;
use Meritum\StructuredLogging\Exception\DomainException;
use Meritum\StructuredLogging\Severity;

final class InvalidTokenException extends DomainException
{
    public function __construct(Throwable $exception, int $code = 0)
    {
        $context = [
            'reason' => $exception->getMessage(),
        ];

        parent::__construct(
            'Invalid or malformed API token',
            Severity::Warning,
            $context,
            false,
            $code,
            $exception
        );
    }

    public function getErrorCode(): string
    {
        return 'API_TOKEN_' . $this->generateErrorCodeSuffix($this->getCode());
    }
}
