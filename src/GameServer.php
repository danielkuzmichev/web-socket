<?php

namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Dispatcher\MessageDispatcherInterface;

class GameServer implements MessageComponentInterface {

    private MessageDispatcherInterface $dispatcher;

    // Конструктор теперь принимает MessageDispatcher как зависимость
    public function __construct(MessageDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if ($data) {
            $this->dispatcher->dispatchFromArray($data, $from);
        } else {
            $from->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Invalid message format']
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
