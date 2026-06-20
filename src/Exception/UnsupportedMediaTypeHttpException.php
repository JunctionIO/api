<?php

namespace Junction\Api\Exception;

use Meritum\Http\Exception\HttpException;

final class UnsupportedMediaTypeHttpException extends HttpException
{
    protected int $status = 415;

    protected string $title = 'Unsupported Media Type';
}
