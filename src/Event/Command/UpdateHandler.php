<?php

namespace Junction\Api\Event\Command;

use Junction\Api\Event\Event;

final class UpdateHandler extends AbstractHandler
{
    /**
     * @throws \Meritum\Database\Exception\ModelNotFoundException
     */
    public function __invoke(Update $command): Event
    {
        $model = $this->repo->findOrFail($command->id);

        $model->description = $command->description;

        $this->repo->save($model);

        return $model;
    }
}
