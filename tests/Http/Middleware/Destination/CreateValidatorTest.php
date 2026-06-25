<?php

namespace Junction\Api\Test\Http\Middleware\Destination;

use Junction\Api\DestinationType\DestinationType;
use Junction\Api\Http\Middleware\Destination\CreateValidator;
use Meritum\Validation\Validator as ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class CreateValidatorTest extends TestCase
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
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['required', 'string'], $rules['name']);
    }

    public function test_description_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['nullable', 'string'], $rules['description']);
    }

    public function test_destination_type_id_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['required', 'string'], $rules['destination_type_id']);
    }

    public function test_status_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['required', 'string', 'in' => ['active', 'disabled']], $rules['status']);
    }

    public function test_events_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['required', 'array'], $rules['events']);
    }

    public function test_events_name_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(
            ['required', 'string', 'regex' => ['/^[a-zA-Z0-9._-]+$/']],
            $rules['events.*.name']
        );
    }

    public function test_config_rule(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertSame(['required', 'array'], $rules['config']);
    }

    public function test_base_rules_only_when_no_destination_type(): void
    {
        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest());

        $this->assertCount(7, $rules);
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

        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
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

        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest($type));

        $this->assertSame(['required', 'string'], $rules['name']);
        $this->assertSame(['required', 'array'], $rules['config']);
    }

    public function test_optional_config_schema_field_omits_required_rule(): void
    {
        $type = new DestinationType([
            'id'            => 'type-uuid',
            'name'          => 'http',
            'queue'         => 'junction.destinations.http',
            'config_schema' => [
                'token' => ['required' => false, 'rules' => ['string']],
            ],
        ]);

        $rules = (new CreateValidator($this->createMock(ValidationService::class)))
            ->rules($this->makeRequest($type));

        $this->assertSame(['string'], $rules['config.token']);
    }
}
