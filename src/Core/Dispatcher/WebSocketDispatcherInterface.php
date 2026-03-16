<?php

namespace App\Core\Dispatcher;

use App\Core\Event\EventInterface;

interface WebSocketDispatcherInterface
{
    public function dispatch(EventInterface $event): void;

    public function dispatchFromArray(array $message): void;

    public function setHandlers(array $handlers): void;
}
