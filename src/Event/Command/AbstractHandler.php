<?php

namespace Junction\Api\Event\Command;

use Junction\Api\Event\EventRepositoryInterface;

abstract class AbstractHandler
{
    public function __construct(protected readonly EventRepositoryInterface $repo) {}
}
