<?php

namespace App\Core\Handler;

use App\Core\Event\EventInterface;
use Ratchet\ConnectionInterface;

interface EventHandlerInterface
{
    public function handle(EventInterface $payload, ?ConnectionInterface $conn = null): void;
}
