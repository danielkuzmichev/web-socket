<?php

namespace App\Core\Dispatcher;

use Ratchet\ConnectionInterface;

interface MessageDispatcherInterface {
    public function dispatch(string $jsonMessage, ConnectionInterface $conn): void;
    public function dispatchFromArray(array $message, ConnectionInterface $conn): void;
    public function setHandlers(array $handlers): void;
}
