<?php

namespace Junction\Api\Test\Bus;

use Georgeff\Database\Contract\DatabaseManagerInterface;
use Junction\Api\Bus\TransactionalCommand;
use Junction\Api\Bus\TransactionMiddleware;
use PHPUnit\Framework\TestCase;

final class TransactionMiddlewareTest extends TestCase
{
    public function test_wraps_transactional_command_in_transaction(): void
    {
        $command = new class implements TransactionalCommand {};

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(fn(callable $cb) => $cb());

        $called = false;
        $next   = function () use (&$called) { $called = true; };

        (new TransactionMiddleware($db))($command, $next);

        $this->assertTrue($called);
    }

    public function test_does_not_wrap_non_transactional_command_in_transaction(): void
    {
        $command = new class {};

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->expects($this->never())->method('transaction');

        (new TransactionMiddleware($db))($command, function () {});
    }

    public function test_returns_result_of_next_for_transactional_command(): void
    {
        $command = new class implements TransactionalCommand {};

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('transaction')->willReturnCallback(fn(callable $cb) => $cb());

        $result = (new TransactionMiddleware($db))($command, fn() => 'result');

        $this->assertSame('result', $result);
    }

    public function test_returns_result_of_next_for_non_transactional_command(): void
    {
        $command = new class {};

        $db = $this->createMock(DatabaseManagerInterface::class);

        $result = (new TransactionMiddleware($db))($command, fn() => 'result');

        $this->assertSame('result', $result);
    }

    public function test_passes_command_to_next(): void
    {
        $command = new class implements TransactionalCommand {};

        $db = $this->createMock(DatabaseManagerInterface::class);
        $db->method('transaction')->willReturnCallback(fn(callable $cb) => $cb());

        $received = null;
        $next     = function (object $cmd) use (&$received) { $received = $cmd; };

        (new TransactionMiddleware($db))($command, $next);

        $this->assertSame($command, $received);
    }
}
