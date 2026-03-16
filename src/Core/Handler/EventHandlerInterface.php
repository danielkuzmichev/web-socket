<?php

namespace App\Core\Handler;

use App\Core\Event\EventInterface;

interface EventHandlerInterface
{
    public function handle(EventInterface $event): void;

    public function getEventClass(): string;
}
