<?php

namespace Junction\Api\Exception;

use Throwable;
use Meritum\StructuredLogging\Severity;
use Meritum\StructuredLogging\Exception\DomainException;

final class JunctionDomainException extends DomainException
{
    public function __construct(Throwable $exception)
    {
        $code = $exception->getCode();

        $context = [
            'original_class'   => $exception::class,
            'original_message' => $exception->getMessage(),
            'original_code'    => $code,
            'original_file'    => $exception->getFile(),
            'original_line'    => $exception->getLine(),
        ];

        parent::__construct(
            'The application encountered an unknown error',
            Severity::Error,
            $context,
            false,
            is_int($code) ? $code : 0,
            $exception
        );
    }

    public function getErrorCode(): string
    {
        return 'JUNCTION_0000';
    }
}
