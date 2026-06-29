<?php

namespace Junction\Api\Exception;

use Meritum\Http\Exception\HttpException;

final class UnauthorizedHttpException extends HttpException
{
    protected int $status = 401;

    protected string $title = 'Unauthroized';
}
