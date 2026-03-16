<?php

namespace App\Infrastructure\Connection;

use Ratchet\ConnectionInterface;

interface ConnectionStorageInterface
{
    public function addConnection(ConnectionInterface $conn): void;

    public function add(string $sessionId, ConnectionInterface $conn): void;

    public function remove(ConnectionInterface $conn): void;

    public function getConnections(string $sessionId): array;

    public function broadcastToSession(string $sessionId, array $message): void;

    public function bindToken(string $playerToken, ConnectionInterface $conn): void;

    public function getByToken(string $playerToken): ?ConnectionInterface;

    public function sendToToken(string $playerToken, array $message): void;

    public function getTokenByConnection(ConnectionInterface $conn): ?string;

}
