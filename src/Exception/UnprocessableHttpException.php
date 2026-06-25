<?php

namespace Junction\Api\Exception;

use Meritum\Http\Exception\HttpException;

final class UnprocessableHttpException extends HttpException
{
    protected int $status = 422;

    protected string $title = 'Unprocessable Content';
}
