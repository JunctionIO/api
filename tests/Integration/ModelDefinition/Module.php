<?php

namespace Junction\Api\Test\Integration\ModelDefinition;

use Faker\Generator;
use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Meritum\ModelFactory\FactoryOption;
use Georgeff\Kernel\Module\ModuleInterface;

final class Module implements ModuleInterface
{
    /**
     * @var class-string[]
     */
    private array $definitions = [
        EventDefinition::class,
        EventLogDefinition::class,
        DestinationDefinition::class,
        DestinationLogDefinition::class,
        DestinationTypeDefinition::class,
    ];

    public function register(KernelInterface $kernel): void
    {
        foreach ($this->definitions as $definition) {
            $kernel->define(
                $definition,
                fn(ContainerInterface $c) => new $definition($c->get(Generator::class))
            )->tag(FactoryOption::DefinitionTag->value);
        }
    }
}
