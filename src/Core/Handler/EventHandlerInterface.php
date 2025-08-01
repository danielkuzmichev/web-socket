<?php

namespace App\Core\Handler;

use App\Core\Event\EventInterface;
use Ratchet\ConnectionInterface;

interface EventHandlerInterface
{
    public function handle(EventInterface $event, ?ConnectionInterface $conn = null): void;

    public function getEventClass(): string;
}
