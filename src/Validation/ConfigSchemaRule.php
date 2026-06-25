<?php

namespace Junction\Api\Validation;

use Meritum\Validation\RuleInterface;

final class ConfigSchemaRule implements RuleInterface
{
    public function name(): string
    {
        return 'config_schema';
    }

    public function validate(mixed $value, mixed ...$params): bool
    {
        if (false === is_array($value)) {
            return false;
        }

        foreach ($value as $key => $definition) {
            if (!is_string($key)) {
                return false;
            }

            if (!is_array($definition)) {
                return false;
            }

            if (!array_key_exists('required', $definition) || !is_bool($definition['required'])) {
                return false;
            }

            if (!array_key_exists('rules', $definition) || !is_array($definition['rules'])) {
                return false;
            }

            foreach ($definition['rules'] as $key => $entry) {
                if (is_int($key) && !is_string($entry)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message(string $attribute, mixed ...$params): string
    {
        return "The {$attribute} is an invalid format";
    }
}
