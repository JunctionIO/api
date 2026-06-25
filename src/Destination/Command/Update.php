<?php

namespace Junction\Api\Destination\Command;

use Junction\Api\Destination\Destination;

final class Update
{
    /**
     * @param array{
     *      name?: string,
     *      description?: string|null,
     *      status?: string,
     *      config?: array<string, mixed>
     * } $data
     */
    public function __construct(
        public readonly Destination $model,
        public readonly array $data
    ) {}
}
