<?php

namespace Junction\Api\Test\Integration\Database;

use Faker\Generator;
use DateTimeImmutable;
use Junction\Api\Event\Event;
use Meritum\Database\Support\Uuid;
use Junction\Api\EventLog\EventLog;
use Junction\Api\Destination\Destination;
use Junction\Api\DestinationLog\DestinationLog;
use Junction\Api\DestinationType\DestinationType;

final class FactoryDefinitions
{
    public static function define(ModelFactory $factory): void
    {
        $factory->define(Destination::class, function (Generator $faker) {
            return [
                'id'                  => Uuid::v7(),
                'name'                => $faker->word,
                'description'         => $faker->sentence,
                'destination_type_id' => Uuid::v7(),
                'config'              => [],
                'status'              => 'active',
                'created_at'          => new DateTimeImmutable(),
                'updated_at'          => new DateTimeImmutable(),
            ];
        });

        $factory->define(DestinationLog::class, function (Generator $faker) {
            return [
                'id'             => Uuid::v7(),
                'trace_id'       => Uuid::v4(),
                'event_log_id'   => Uuid::v7(),
                'destination_id' => Uuid::v7(),
                'status'         => 'pending',
                'attempted_at'   => new DateTimeImmutable(),
                'error'          => null,
                'created_at'     => new DateTimeImmutable(),
                'updated_at'     => new DateTimeImmutable(),
            ];
        });

        $factory->define(DestinationType::class, function (Generator $faker) {
            return [
                'id'            => Uuid::v7(),
                'name'          => $faker->unique()->word,
                'queue'         => $faker->unique()->slug(1),
                'description'   => $faker->sentence,
                'config_schema' => [],
                'created_at'    => new DateTimeImmutable(),
                'updated_at'    => new DateTimeImmutable(),
            ];
        });

        $factory->define(Event::class, function (Generator $faker) {
            return [
                'id'          => Uuid::v7(),
                'name'        => $faker->unique()->word,
                'description' => $faker->sentence,
                'created_at'  => new DateTimeImmutable(),
                'updated_at'  => new DateTimeImmutable(),
            ];
        });

        $factory->define(EventLog::class, function (Generator $faker) {
            return [
                'id'          => Uuid::v7(),
                'trace_id'    => Uuid::v4(),
                'event_id'    => Uuid::v7(),
                'payload'     => [],
                'source_ip'   => $faker->ipv4,
                'auth_id'     => Uuid::v4(),
                'received_at' => new DateTimeImmutable(),
                'created_at'  => new DateTimeImmutable(),
            ];
        });
    }
}
