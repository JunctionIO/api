<?php

namespace Junction\Api\Test\Unit\Context;

use Junction\Api\Context\EnvironmentEnricher;
use PHPUnit\Framework\TestCase;

final class EnvironmentEnricherTest extends TestCase
{
    public function test_adds_hostname_to_context(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertArrayHasKey('hostname', $result);
        $this->assertIsString($result['hostname']);
    }

    public function test_hostname_falls_back_to_unknown(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertNotSame('', $result['hostname']);
    }

    public function test_adds_pid_to_context(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertArrayHasKey('pid', $result);
        $this->assertIsInt($result['pid']);
    }

    public function test_adds_php_version_to_context(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertArrayHasKey('php_version', $result);
        $this->assertSame(PHP_VERSION, $result['php_version']);
    }

    public function test_adds_environment_key_to_context(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertArrayHasKey('environment', $result);
        $this->assertIsArray($result['environment']);
    }

    public function test_reads_configured_env_vars(): void
    {
        putenv('APP_ENV=testing');

        $result = (new EnvironmentEnricher(['APP_ENV']))->enrich([]);

        $this->assertSame('testing', $result['environment']['APP_ENV']);

        putenv('APP_ENV');
    }

    public function test_missing_env_var_is_null_in_environment_array(): void
    {
        putenv('JUNCTION_TEST_MISSING_VAR');

        $result = (new EnvironmentEnricher(['JUNCTION_TEST_MISSING_VAR']))->enrich([]);

        $this->assertNull($result['environment']['JUNCTION_TEST_MISSING_VAR']);
    }

    public function test_uses_custom_vars_list(): void
    {
        $result = (new EnvironmentEnricher(['CUSTOM_VAR_A', 'CUSTOM_VAR_B']))->enrich([]);

        $this->assertArrayHasKey('CUSTOM_VAR_A', $result['environment']);
        $this->assertArrayHasKey('CUSTOM_VAR_B', $result['environment']);
        $this->assertArrayNotHasKey('APP_ENV', $result['environment']);
    }

    public function test_does_not_overwrite_existing_context_keys(): void
    {
        $result = (new EnvironmentEnricher())->enrich(['hostname' => 'my-custom-host']);

        $this->assertSame('my-custom-host', $result['hostname']);
    }

    public function test_preserves_existing_context_entries(): void
    {
        $result = (new EnvironmentEnricher())->enrich(['request_id' => 'abc-123']);

        $this->assertSame('abc-123', $result['request_id']);
    }

    public function test_default_vars_include_app_env_name_and_version(): void
    {
        $result = (new EnvironmentEnricher())->enrich([]);

        $this->assertArrayHasKey('APP_ENV', $result['environment']);
        $this->assertArrayHasKey('APP_NAME', $result['environment']);
        $this->assertArrayHasKey('APP_VERSION', $result['environment']);
    }
}
