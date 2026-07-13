<?php

namespace Junction\Api\Test\Integration\Database;

use Faker\Factory;
use Faker\Generator;
use Meritum\Database\Model;
use Meritum\Database\Support\Collection;
use Georgeff\Database\Contract\DatabaseManagerInterface;

/**
 * @template T of Model
 */
final class ModelFactory
{
    private readonly Generator $faker;

    /**
     * @var array<class-string, callable(Generator): array>
     */
    private array $definitions = [];

    public function __construct(private readonly DatabaseManagerInterface $db)
    {
        $this->faker = Factory::create();
    }

    /**
     * @param class-string $class
     * @param callable(Generator): array
     */
    public function define(string $class, callable $definition): void
    {
        $this->definitions[$class] = $definition;
    }

    /**
     * Create and hydrate a model instance without persisting
     *
     * @param class-string         $class
     * @param array<string, mixed> $attributes
     *
     * @return T
     */
    public function make(string $class, array $attributes = []): Model
    {
        $definition = $this->definitions[$class] ?? null;

        if (null === $definition) {
            throw new \RuntimeException("Definition for model class [{$class}] was not found");
        }

        $arr = $definition($this->faker);

        if (!is_array($arr)) {
            throw new \RuntimeException("Definition for model class [{$class}] must return an array");
        }

        return new $class($attributes + $arr);
    }

    /**
     * Create and persist the model to the database
     *
     * @param class-string         $class
     * @param array<string, mixed> $attributes
     *
     * @return T
     */
    public function create(string $class, array $attributes = []): Model
    {
        $model = $this->make($class, $attributes);

        $query = $this->db->insert()->into($model->getTable())->addRow($model->getDirty());

        $this->db->fetchAffected($query);

        if ($model->isIncrementing()) {
            $id = $this->db->lastInsertId();

            if (null !== $id) {
                $id = ('int' === $model->getPrimaryKeyType()) ? (int) $id : $id;

                $model->setPrimaryKeyValue($id);
            }
        }

        return $model;
    }

    /**
     * Create a collection of models
     *
     * @param class-string         $class
     * @param array<string, mixed> $attributes
     *
     * @return Collection<T>
     */
    public function collection(int $count, string $class, array $attributes = [], bool $persist = true): Collection
    {
        $method = $persist ? 'create' : 'make';

        $models = [];

        for ($i = 0; $i < $count; $i++) {
            /** @var Model */
            $model = $this->{$method}($class, $attributes);

            $pk = $model->getPrimaryKeyValue();

            $key = null !== $pk ? $pk : $i;

            $models[$key] = $model;
        }

        return new Collection($models);
    }
}
