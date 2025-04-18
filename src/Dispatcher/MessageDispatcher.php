<?php

namespace App\Dispatcher;

use Ratchet\ConnectionInterface;
use App\Handler\MessageHandlerInterface;

class MessageDispatcher implements MessageDispatcherInterface {

    private array $handlers = [];

    public function __construct(iterable $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getType()] = $handler;
        }
    }

    public function dispatch(string $jsonMessage, ConnectionInterface $conn): void {
        $data = json_decode($jsonMessage, true);

        if (!isset($data['type']) || !isset($this->handlers[$data['type']])) {
            $conn->send(json_encode(['error' => 'Unknown message type']));
            return;
        }

        $this->handlers[$data['type']]->handle($data['payload'] ?? [], $conn);
    }

    public function dispatchFromArray(array $message, ConnectionInterface $conn): void {
        $json = json_encode($message);
        $this->dispatch($json, $conn);
    }

    public function setHandlers(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getType()] = $handler;
        }
    }

    public function registerHandler(MessageHandlerInterface $handler): void
    {
        $this->handlers[$handler->getType()] = $handler;
    }
}
