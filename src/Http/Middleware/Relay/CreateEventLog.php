<?php

namespace Junction\Api\Http\Middleware\Relay;

use Junction\Api\Event\Event;
use Junction\Api\Trace\TraceId;
use Junction\Api\ApiToken\Token;
use Junction\Api\EventLog\EventLog;
use Georgeff\Bus\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Junction\Api\EventLog\Command\Create;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateEventLog implements MiddlewareInterface
{
    public function __construct(private readonly DispatcherInterface $dispatcher) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $log = $this->dispatcher->dispatch(new Create(
            $this->getTraceId($request),
            $this->getEventId($request),
            $this->getAuthId($request),
            $this->getSourceIp($request),
            $this->getPayload($request)
        ));

        $request = $request->withAttribute(EventLog::class, $log);

        return $handler->handle($request);
    }

    private function getTraceId(ServerRequestInterface $request): string
    {
        $trace = $request->getAttribute(TraceId::class);

        assert($trace instanceof TraceId);

        return (string) $trace;
    }

    private function getEventId(ServerRequestInterface $request): string
    {
        $event = $request->getAttribute(Event::class);

        assert($event instanceof Event);

        return $event->id;
    }

    private function getAuthId(ServerRequestInterface $request): string
    {
        $token = $request->getAttribute(Token::class);

        assert($token instanceof Token);

        return $token->id;
    }

    private function getSourceIp(ServerRequestInterface $request): ?string
    {
        $ip = $request->getHeaderLine('X-Client-IP');

        return '' !== $ip ? $ip : null;
    }

    /**
     * @return array<mixed>
     */
    private function getPayload(ServerRequestInterface $request): array
    {
        $input = $request->getParsedBody();

        $payload = is_array($input) ? ($input['payload'] ?? null) : null;

        assert(is_array($payload));

        return $payload;
    }
}
