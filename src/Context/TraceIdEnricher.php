<?php

namespace Junction\Api\Context;

use Junction\Api\Trace\TraceId;
use Psr\Http\Message\ServerRequestInterface;
use Meritum\StructuredLogging\ContextEnricher;

final class TraceIdEnricher implements ContextEnricher
{
    public function __construct(
        private readonly TraceId $trace,
        private readonly ServerRequestInterface $request
    ) {}

    public function enrich(array $context): array
    {
        if ('/relay' !== $this->request->getUri()->getPath()) {
            return $context;
        }

        return $context + ['trace_id' => $this->trace->id];
    }
}
