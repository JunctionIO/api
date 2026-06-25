<?php

namespace Junction\Api\Event\Command;

use Junction\Api\Event\Event;
use Meritum\Database\Support\Collection;
use Junction\Api\Event\EventRepositoryInterface;

final class FindManyOrCreateHandler
{
    public function __construct(private readonly EventRepositoryInterface $repo) {}

    /**
     * @return Collection<Event>
     */
    public function __invoke(FindManyOrCreate $command): Collection
    {
        $names = [];

        foreach ($command->events as $event) {
            $names[] = $event['name'];
        }

        $collection = $this->repo->getByName($names);

        $existingNames = [];

        $collection->each(function (Event $model) use (&$existingNames) {
            $existingNames[] = $model->name;
        });

        $toSave = [];

        foreach ($names as $name) {
            if (!in_array($name, $existingNames, true)) {
                $m = new Event();
                $m->name = $name;

                $toSave[] = $m;
            }
        }

        if ([] !== $toSave) {
            $new = $this->repo->insertMany($toSave);

            $collection = $collection->merge($new);
        }

        return $collection;
    }
}
