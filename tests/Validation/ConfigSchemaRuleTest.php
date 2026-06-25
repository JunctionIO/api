<?php

namespace Junction\Api\Test\Validation;

use Junction\Api\Validation\ConfigSchemaRule;
use PHPUnit\Framework\TestCase;

final class ConfigSchemaRuleTest extends TestCase
{
    private function rule(): ConfigSchemaRule
    {
        return new ConfigSchemaRule();
    }

    private function validDefinition(): array
    {
        return ['required' => true, 'rules' => ['string']];
    }

    public function test_name_returns_config_schema(): void
    {
        $this->assertSame('config_schema', $this->rule()->name());
    }

    public function test_returns_true_for_empty_schema(): void
    {
        $this->assertTrue($this->rule()->validate([]));
    }

    public function test_returns_true_for_valid_schema(): void
    {
        $this->assertTrue($this->rule()->validate([
            'url'    => ['required' => true, 'rules' => ['string', 'url']],
            'method' => ['required' => false, 'rules' => ['string', 'in' => ['GET', 'POST']]],
        ]));
    }

    public function test_returns_false_when_value_is_not_array(): void
    {
        $this->assertFalse($this->rule()->validate('not-an-array'));
    }

    public function test_returns_false_when_key_is_not_string(): void
    {
        $this->assertFalse($this->rule()->validate([
            0 => $this->validDefinition(),
        ]));
    }

    public function test_returns_false_when_definition_is_not_array(): void
    {
        $this->assertFalse($this->rule()->validate([
            'url' => 'not-an-array',
        ]));
    }

    public function test_returns_false_when_required_is_missing(): void
    {
        $this->assertFalse($this->rule()->validate([
            'url' => ['rules' => ['string']],
        ]));
    }

    public function test_returns_false_when_required_is_not_bool(): void
    {
        $this->assertFalse($this->rule()->validate([
            'url' => ['required' => 'yes', 'rules' => ['string']],
        ]));
    }

    public function test_returns_false_when_rules_is_missing(): void
    {
        $this->assertFalse($this->rule()->validate([
            'url' => ['required' => true],
        ]));
    }

    public function test_returns_false_when_rules_is_not_array(): void
    {
        $this->assertFalse($this->rule()->validate([
            'url' => ['required' => true, 'rules' => 'string'],
        ]));
    }

    public function test_returns_true_for_parameterized_rule_entry(): void
    {
        $this->assertTrue($this->rule()->validate([
            'method' => ['required' => false, 'rules' => ['string', 'in' => ['GET', 'POST']]],
        ]));
    }

    public function test_returns_false_when_integer_keyed_rule_entry_is_not_string(): void
    {
        $this->assertFalse($this->rule()->validate([
            'count' => ['required' => true, 'rules' => ['integer', 'min' => 1, 42]],
        ]));
    }

    public function test_message_includes_attribute(): void
    {
        $this->assertStringContainsString('config_schema', $this->rule()->message('config_schema'));
    }
}
