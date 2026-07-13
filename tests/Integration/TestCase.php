<?php

namespace Junction\Api\Test\Integration;

use Firebase\JWT\JWT;
use Georgeff\Container\Container;
use Laminas\Diactoros\Stream;
use Meritum\Cli\CliKernel;
use Meritum\Http\HttpKernel;
use Georgeff\Kernel\Support\Env;
use Georgeff\Kernel\Environment;
use Meritum\Cli\CliKernelInterface;
use Laminas\Diactoros\ServerRequest;
use Meritum\Http\HttpKernelInterface;
use Junction\Api\Queue\QueueInterface;
use Georgeff\Database\DatabaseManager;
use Georgeff\Database\Connection\SqliteDriver;
use Symfony\Component\Console\Input\ArrayInput;
use Georgeff\Database\Connection\ConnectionManager;
use Junction\Api\ModuleRepository as ApiModuleRepo;
use Junction\Cli\ModuleRepository as CliModuleRepo;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private HttpKernelInterface $httpKernel;

    private CliKernelInterface $cliKernel;

    protected Database\ModelFactory $mf;

    /**
     * @var callable[]
     */
    private array $tearDownCallbacks = [];

    protected function setUp(): void
    {
        $this->loadEnvironment();

        $this->httpKernel = new HttpKernel(Environment::Testing);

        $this->httpKernel->addRepository(new ApiModuleRepo());

        $this->cliKernel = new CliKernel(Environment::Testing);

        $this->cliKernel->addRepository(new CliModuleRepo());

        $this->mf = $this->getModelFactory();

        Database\FactoryDefinitions::define($this->mf);

        $this->migrate();

        // Boot eagerly so overrides (see getMemoryQueue()) can swap definitions directly
        // on the built container instead of racing module registration via define().
        $this->httpKernel->boot();

        $this->onTearDown(function () {
            $this->httpKernel->shutdown();
            $this->cliKernel->shutdown();
        });
    }

    private function loadEnvironment(): void
    {
        $env = [
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

        foreach ($env as $name => $value) {
            putenv("$name=$value");
        }
    }

    protected function get(string $uri, array $headers = [], array $queryParams = []): Http\TestResponse
    {
        return $this->request(__FUNCTION__, $uri, [], $headers, $queryParams);
    }

    protected function post(string $uri, array $parsedBody = [], array $headers = [], array $queryParams = []): Http\TestResponse
    {
        return $this->request(__FUNCTION__, $uri, $parsedBody, $headers, $queryParams);
    }

    protected function put(string $uri, array $parsedBody = [], array $headers = [], array $queryParams = []): Http\TestResponse
    {
        return $this->request(__FUNCTION__, $uri, $parsedBody, $headers, $queryParams);
    }

    protected function patch(string $uri, array $parsedBody = [], array $headers = [], array $queryParams = []): Http\TestResponse
    {
        return $this->request(__FUNCTION__, $uri, $parsedBody, $headers, $queryParams);
    }

    protected function delete(string $uri, array $parsedBody = [], array $headers = [], array $queryParams = []): Http\TestResponse
    {
        return $this->request(__FUNCTION__, $uri, $parsedBody, $headers, $queryParams);
    }

    protected function request(string $method, string $uri, array $parsedBody = [], array $headers = [], array $queryParams = []): Http\TestResponse
    {
        $method = strtoupper($method);

        $body = new Stream('php://temp', 'wb+');

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $headers['Content-Type'] ??= 'application/json';

            $body->write(json_encode($parsedBody, JSON_THROW_ON_ERROR));
            $body->rewind();
        }

        $request = new ServerRequest(
            [],
            [],
            '/' . ltrim($uri, '/'),
            $method,
            $body,
            $headers,
            [],
            $queryParams
        );

        $response = $this->httpKernel->handle($request->withParsedBody($parsedBody));

        return new Http\TestResponse($response);
    }

    private function migrate(): void
    {
        $this->command('migration:run');

        $this->onTearDown(fn() => $this->command('migration:rollback'));
    }

    private function getModelFactory(): Database\ModelFactory
    {
        $driver = new SqliteDriver(Env::get('DB_DATABASE'));

        $conn = new ConnectionManager($driver);

        $manager = new DatabaseManager($conn);

        return new Database\ModelFactory($manager);
    }

    protected function command(string $command, array $parameters = []): BufferedOutput
    {
        if (false === $this->cliKernel->isBooted()) {
            $this->cliKernel->boot();
        }

        $parameters['command'] = $command;

        $output = new BufferedOutput();

        $this->cliKernel->handle(new ArrayInput($parameters), $output);

        return $output;
    }

    protected function apiToken(string $type, string $id = 'test'): string
    {
        $payload = [
            'type' => $type,
            'id'   => $id,
            'iat'  => (new \DateTimeImmutable())->getTimestamp(),
        ];

        return JWT::encode($payload, (string) Env::get('JWT_SECRET'), 'HS256');
    }

    protected function getMemoryQueue(): Queue\MemoryQueue
    {
        $queue = new Queue\MemoryQueue();

        $container = $this->httpKernel->getContainer();

        assert($container instanceof Container);

        // Swapping directly on the already-built container (httpKernel is booted
        // eagerly in setUp()) instead of kernel->define() pre-boot: a module's own
        // moduleRegistration-phase define() for the same ID always runs after any
        // pre-boot test code and would silently win, so pre-boot define() can't
        // reliably override a module-registered service. See project-meritum-testing-plan.
        $container->add(QueueInterface::class, fn() => $queue, shared: true);

        return $queue;
    }

    protected function onTearDown(callable $callback): void
    {
        $this->tearDownCallbacks[] = $callback;
    }

    protected function tearDown(): void
    {
        foreach ($this->tearDownCallbacks as $callback) {
            $callback();
        }

        // Kernel::shutdown() only fires lifecycle hooks - it never closes the underlying
        // PDO connection, and the kernel's own KernelInterface::class => fn() => $this
        // self-registration creates a reference cycle (kernel -> container -> resolved
        // services -> kernel) that plain refcounting can't free. Without forcing collection
        // here, the shared-cache in-memory SQLite DB survives into the next test at the
        // mercy of PHP's GC schedule instead of being deterministically dropped per test.
        unset($this->httpKernel, $this->cliKernel, $this->mf);

        gc_collect_cycles();
    }
}
