<?php

namespace Junction\Api\Test\Integration;

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

final class TestCase extends \PHPUnit\Framework\TestCase
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

        $this->onTearDown(function () {
            $this->httpKernel->shutdown();
            $this->cliKernel->shutdown();
        });
    }

    private function loadEnvironment(): void
    {
        $env = [
            'APP_ENV'     => 'testing',
            'APP_NAME'    => 'junction',
            'APP_DEBUG'   => 'false',
            'APP_VERSION' => 'dev',
            'LOG_LEVEL'   => 'debug',
            'DB_DRIVER'   => 'sqlite',
            'DB_DATABASE' => 'file::memory:?cache=shared',
            'JWT_SECRET'  => 'test-secret-key-at-least-32-chars',
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
        if (false === $this->httpKernel->isBooted()) {
            $this->httpKernel->boot();
        }

        $method = strtoupper($method);

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $headers['Content-Type'] ??= 'application/json';
        }

        $request = new ServerRequest(
            [],
            [],
            '/' . ltrim($uri, '/'),
            $method,
            'php://temp',
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

    protected function getMemoryQueue(): Queue\MemoryQueue
    {
        $queue = new Queue\MemoryQueue();

        $this->httpKernel->define(QueueInterface::class, fn() => $queue)->share();

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
    }
}
