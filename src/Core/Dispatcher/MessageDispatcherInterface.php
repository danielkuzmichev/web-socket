<?php

namespace App\Core\Dispatcher;

use Ratchet\ConnectionInterface;

interface MessageDispatcherInterface
{
    public function dispatch(string $jsonMessage, ?ConnectionInterface $conn = null): void;

    public function dispatchFromArray(array $message, ?ConnectionInterface $conn = null): void;

    public function setHandlers(array $handlers): void;
}
