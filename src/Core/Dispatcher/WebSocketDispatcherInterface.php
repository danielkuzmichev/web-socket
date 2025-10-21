<?php

namespace App\Core\Dispatcher;

use App\Core\Event\EventInterface;
use Ratchet\ConnectionInterface;

interface WebSocketDispatcherInterface
{
    public function dispatch(EventInterface $event, ?ConnectionInterface $conn = null): void;

    public function dispatchFromArray(array $message, ?ConnectionInterface $conn = null): void;

    public function setHandlers(array $handlers): void;
}
