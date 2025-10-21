<?php

namespace App\Infrastructure\Connection;

use Ratchet\ConnectionInterface;

class ConnectionStorage implements ConnectionStorageInterface
{
    private array $connections = [];

    public function add(string $sessionId, ConnectionInterface $conn): void
    {
        $this->connections[$sessionId][$conn->resourceId] = $conn;
    }

    public function remove(ConnectionInterface $conn): void
    {
        foreach ($this->connections as $sessionId => $clients) {
            if (isset($clients[$conn->resourceId])) {
                unset($this->connections[$sessionId][$conn->resourceId]);
                if (empty($this->connections[$sessionId])) {
                    unset($this->connections[$sessionId]);
                }
                return;
            }
        }
    }

    public function getConnections(string $sessionId): array
    {
        return $this->connections[$sessionId] ?? [];
    }

    public function broadcastToSession(string $sessionId, array $message): void
    {
        $connections = $this->getConnections($sessionId);
        foreach ($connections as $conn) {
            $conn->send(json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        }
    }
}
