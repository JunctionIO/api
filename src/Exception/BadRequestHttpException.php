<?php

namespace Junction\Api\Exception;

use Meritum\Http\Exception\HttpException;

final class BadRequestHttpException extends HttpException
{
    protected int $status = 400;

    protected string $title = 'Bad Request';
}
