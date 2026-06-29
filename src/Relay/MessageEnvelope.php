<?php

namespace Junction\Api\Relay;

use Junction\Api\Destination\Destination;

final class MessageEnvelope implements \JsonSerializable
{
    /**
     * @var array{
     *      trace_id: string,
     *      log_id: string,
     *      destination: array{name: string, config: array<string, mixed>}
     * }
     */
    public readonly array $meta;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(public readonly array $payload, string $traceId, string $logId, Destination $destination)
    {
        $this->meta = [
            'trace_id'    => $traceId,
            'log_id'      => $logId,
            'destination' => [
                'name'   => $destination->name,
                'config' => $destination->config,
            ],
        ];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'payload' => $this->payload,
            'meta'    => $this->meta,
        ];
    }
}
