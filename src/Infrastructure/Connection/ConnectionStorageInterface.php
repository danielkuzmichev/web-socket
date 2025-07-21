<?php

namespace App\Infrastructure\Connection;

use Ratchet\ConnectionInterface;

interface ConnectionStorageInterface
{
    public function add(string $sessionId, ConnectionInterface $conn): void;

    public function remove(ConnectionInterface $conn): void;

    public function getConnections(string $sessionId): array;

    public function broadcastToSession(string $sessionId, array $message): void;

}
