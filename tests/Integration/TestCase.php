<?php

namespace Junction\Api\Test\Integration;

use Firebase\JWT\JWT;
use Meritum\Cli\CliKernel;
use Psr\Log\LoggerInterface;
use Meritum\Http\HttpKernel;
use Georgeff\Kernel\Support\Env;
use Georgeff\Kernel\Environment;
use Meritum\Cli\CliKernelInterface;
use Meritum\Database\DatabaseModule;
use Georgeff\Kernel\KernelInterface;
use Meritum\Http\HttpKernelInterface;
use Junction\Api\Queue\QueueInterface;
use Meritum\HttpTesting\HttpTestingTrait;
use Meritum\Testing\TestingKernelInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Meritum\ModelFactory\ModelFactoryModule;
use Meritum\ModelFactory\ModelFactoryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Junction\Api\ModuleRepository as ApiModules;
use Junction\Cli\ModuleRepository as CliModules;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class TestCase extends \Meritum\Testing\TestCase
{
    use HttpTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel->manages(
            new HttpKernel(Environment::Testing)->addRepository(new ApiModules()),
            HttpKernelInterface::class
        );

        $this->kernel->manages(
            new CliKernel(Environment::Testing)->addRepository(new CliModules()),
            CliKernelInterface::class
        );

        $this->kernel->instance(LoggerInterface::class, new Logging\LogSpy(), HttpKernelInterface::class);

        $this->migrate();
    }

    protected function modules(): array
    {
        return [
            new DatabaseModule(),
            new ModelFactoryModule(),
            new ModelDefinition\Module(),
        ];
    }

    protected function environment(): array
    {
        return [
            'APP_ENV'            => 'testing',
            'APP_NAME'           => 'junction',
            'APP_DEBUG'          => 'false',
            'APP_VERSION'        => 'dev',
            'LOG_LEVEL'          => 'debug',
            'DB_DRIVER'          => 'sqlite',
            'DB_DATABASE'        => 'file::memory:?cache=shared',
            'DB_MIGRATIONS_PATH' => dirname(__DIR__, 2) . '/vendor/junction/cli/migrations',
            'JWT_SECRET'         => 'test-secret-key-at-least-32-chars',
        ];
    }

    protected function getRequestHandler(): RequestHandlerInterface
    {
        $this->bootIfNotBooted();

        $kernel = $this->kernel->get(HttpKernelInterface::class);

        assert($kernel instanceof RequestHandlerInterface);

        return $kernel;
    }

    protected function bootIfNotBooted(): void
    {
        if (!$this->kernel->isBooted()) {
            $this->kernel->boot();
        }
    }

    protected function apiToken(string $type, string $id = 'test'): string
    {
        $this->bootIfNotBooted();

        $payload = [
            'type' => $type,
            'id'   => $id,
            'iat'  => new \DateTimeImmutable()->getTimestamp(),
        ];

        return JWT::encode($payload, (string) Env::get('JWT_SECRET'), 'HS256');
    }

    protected function getMemoryQueue(): Queue\MemoryQueue
    {
        $queue = new Queue\MemoryQueue();

        $this->kernel->instance(QueueInterface::class, $queue, HttpKernelInterface::class);

        return $queue;
    }

    protected function getModelFactory(): ModelFactoryInterface
    {
        $this->bootIfNotBooted();

        return $this->kernel->getContainer()->get(ModelFactoryInterface::class);
    }

    private function migrate(): void
    {
        $this->kernel->onBooted(function (KernelInterface $k) {
            assert($k instanceof TestingKernelInterface);

            $cli = $k->get(CliKernelInterface::class);

            assert($cli instanceof CliKernelInterface);

            $cli->handle(new ArrayInput(['command' => 'migration:run']), new BufferedOutput());
        });

        $this->kernel->onShutdown(function (KernelInterface $k) {
            assert($k instanceof TestingKernelInterface);

            $cli = $k->get(CliKernelInterface::class);

            assert($cli instanceof CliKernelInterface);

            $cli->handle(new ArrayInput(['command' => 'migration:rollback']), new BufferedOutput());
        });
    }
}
