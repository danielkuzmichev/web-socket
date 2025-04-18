<?php

namespace App\Util\Connection;

use Ratchet\ConnectionInterface;

class ConnectionStorage
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
}
