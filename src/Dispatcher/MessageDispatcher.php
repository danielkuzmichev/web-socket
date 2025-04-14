<?php

namespace App\Dispatcher;

use Ratchet\ConnectionInterface;
use App\Handler\MessageHandlerInterface;

class MessageDispatcher {
    private array $handlers = [];

    public function __construct() {
        foreach (glob(__DIR__ . '/../Handler/*Handler.php') as $file) {
            require_once $file;
            $className = 'App\\Handler\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof MessageHandlerInterface) {
                    $this->handlers[$instance->getType()] = $instance;
                }
            }
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
}
