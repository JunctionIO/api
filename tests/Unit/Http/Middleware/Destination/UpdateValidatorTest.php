<?php

namespace Junction\Api\Test\Unit\Http\Middleware\Destination;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\Destination\UpdateValidator;
use Meritum\Validation\Validator as ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateValidatorTest extends TestCase
{
    private function makeRequest(?DestinationType $type = null): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(DestinationType::class)
            ->willReturn($type);

        return $request;
    }

    public function test_name_rule(): void
    {
        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['string'], $rules['name']);
    }

    public function test_description_rule(): void
    {
        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['nullable', 'string'], $rules['description']);
    }

    public function test_status_rule(): void
    {
        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['string', 'in' => ['active', 'disabled']], $rules['status']);
    }

    public function test_config_rule(): void
    {
        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['array'], $rules['config']);
    }

    public function test_base_rules_only_when_no_destination_type(): void
    {
        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertCount(4, $rules);
    }

    public function test_merges_config_schema_rules_when_destination_type_is_present(): void
    {
        $type = new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [
                'url' => ['required' => true, 'rules' => ['string', 'url']],
            ],
        ]);

        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest($type));

        $this->assertSame(['required', 'string', 'url'], $rules['config.url']);
    }

    public function test_config_schema_rules_do_not_overwrite_base_rules(): void
    {
        $type = new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [
                'url' => ['required' => true, 'rules' => ['string', 'url']],
            ],
        ]);

        $rules = (new UpdateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest($type));

        $this->assertSame(['array'], $rules['config']);
        $this->assertSame(['string'], $rules['name']);
    }
}
