<?php

namespace App\Core\Dispatcher;

use App\Core\Handler\MessageHandlerInterface;
use Ratchet\ConnectionInterface;

class MessageDispatcher implements MessageDispatcherInterface {

    /**
     * @var MessageHandlerInterface[]
     */
    private array $handlers = [];

    /**
     * @param iterable<MessageHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function dispatch(string $jsonMessage, ?ConnectionInterface $conn = null): void {
        $data = json_decode($jsonMessage, true);

        $this->dispatchFromArray($data, $conn);
    }

    public function dispatchFromArray(array $message, ?ConnectionInterface $conn = null): void {
        if (!isset($message['type']) || !isset($this->handlers[$message['type']])) {
            $conn->send(json_encode(['error' => 'Unknown message type']));
            return;
        }

        $this->handlers[$message['type']]->handle($message['payload'] ?? [], $conn);
    }

    public function setHandlers(iterable $handlers): void
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
