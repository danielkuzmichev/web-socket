<?php

namespace App\Dispatcher;

use Ratchet\ConnectionInterface;

interface MessageDispatcherInterface {
    public function dispatch(string $jsonMessage, ConnectionInterface $conn): void;
    public function dispatchFromArray(array $message, ConnectionInterface $conn): void;
}
