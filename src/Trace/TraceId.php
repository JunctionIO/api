<?php

namespace Junction\Api\Trace;

use Junction\Api\Support\Uuid;

final class TraceId implements \Stringable
{
    public private(set) string $id;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function set(string $uuid): self
    {
        $this->id = $uuid;

        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
