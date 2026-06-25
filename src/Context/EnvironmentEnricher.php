<?php

namespace Junction\Api\Context;

use Georgeff\Kernel\Support\Env;
use Meritum\StructuredLogging\ContextEnricher;

final class EnvironmentEnricher implements ContextEnricher
{
    /**
     * @param string[] $vars
     */
    public function __construct(
        private readonly array $vars = ['APP_ENV', 'APP_NAME', 'APP_VERSION']
    ) {}

    public function enrich(array $context): array
    {
        $env = [];

        foreach ($this->vars as $var) {
            $env[$var] = Env::get($var);
        }

        return $context + [
            'hostname'    => gethostname() ?: 'unknown',
            'pid'         => (int) getmypid(),
            'php_version' => \PHP_VERSION,
            'environment' => $env,
        ];
    }
}
