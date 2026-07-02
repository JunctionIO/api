# Junction API

The Junction PHP API service. Handles event ingest, destination management, and delivery status updates. Built on the [Meritum](https://github.com/MeritumIO) HTTP kernel.

## Dev Environment

Requires [Nix](https://nixos.org/download/) and [devenv](https://devenv.sh/getting-started/).

Copy `.env.example` to `.env`, then:

```bash
devenv shell
composer install
devenv up   # starts PHP server, Postgres, and RabbitMQ
```

## Commands

```bash
composer test     # PHPUnit
composer analyze  # PHPStan
```
