# Virtus

Virtus is a `composer create-project` scaffold for building HTTP APIs with the [Meritum](https://github.com/MeritumIO) ecosystem. It ships a pre-wired [meritum/http](https://github.com/MeritumIO/http) kernel with a clean structure ready to build on.

## Requirements

- PHP 8.4+
- Composer

## Getting Started

```bash
composer create-project meritum/virtus my-app
cd my-app
```

Copy `.env.example` to `.env` and adjust as needed:

```
APP_ENV=local
APP_DEBUG=true
```

## Dev Environment

Virtus ships with a `devenv.nix` for [devenv](https://devenv.sh) — a Nix-based developer environment that provides PHP and Composer without requiring a system install. It also defines a local web server process.

### Prerequisites

1. Install [Nix](https://nixos.org/download/) (the package manager, not the OS)
2. Install [devenv](https://devenv.sh/getting-started/)

### Usage

Enter the development shell:

```bash
devenv shell
```

This activates PHP 8.4, Composer, and `vendor/bin` on your `PATH`. Your `.env` file is loaded automatically.

From inside the shell, install dependencies:

```bash
composer install
```

Start the local web server (PHP built-in server on port 8000):

```bash
devenv up
```

The API is then available at `http://localhost:8000`.

### Customising the environment

Open `devenv.nix` to add PHP extensions or services:

```nix
php = pkgs.php84.withExtensions ({ enabled, all }: enabled ++ [
    all.pdo_pgsql
    all.pdo_mysql
]);
```

```nix
# services.postgres = {
#   enable = true;
#   listen_addresses = "127.0.0.1";
# };
# services.redis.enable = true;
```

Uncomment the services you need — they start alongside the web server when you run `devenv up`.

## Adding a Handler

Create a PSR-15 handler in `src/Handler/`:

```php
namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HomeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['message' => 'Hello, world!']);
    }
}
```

Register the handler and its route in `AppModule::register()`:

```php
use Meritum\Http\HttpKernelInterface;

public function register(KernelInterface $kernel): void
{
    assert($kernel instanceof HttpKernelInterface);

    $kernel->define(Handler\HomeHandler::class, fn() => new Handler\HomeHandler());

    $kernel->addRoute('GET', '/', Handler\HomeHandler::class);
}
```

## Structure

```
www/index.php             Entry point
src/
  ModuleRepository.php    Register application modules
  AppModule.php           Register handlers, routes, and application config
tests/
devenv.nix                Dev environment
```

## Testing

```bash
composer test
```

## Further Reading

- [meritum/http](https://github.com/MeritumIO/http) — HTTP kernel, routing, middleware
- [georgeff/kernel](https://github.com/MikeGeorgeff/kernel) — DI, modules, service tagging
