<?php

namespace Junction\Api\Event\Command;

use Junction\Api\Event\Event;

final class FindOrCreateHandler extends AbstractHandler
{
    public function __invoke(FindOrCreate $command): Event
    {
        $model = $this->repo->findByName($command->name);

        if (null !== $model) {
            return $model;
        }

        $model = new Event();
        $model->name        = $command->name;
        $model->description = $command->description;

        $this->repo->save($model);

        return $model;
    }
}
