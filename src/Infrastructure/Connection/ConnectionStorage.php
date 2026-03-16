<?php

namespace App\Infrastructure\Connection;

use Ratchet\ConnectionInterface;

class ConnectionStorage implements ConnectionStorageInterface
{
    private array $connections = [];
    private array $connectionsById = [];
    private array $tokenToId = [];
    private array $idToTokens = [];

    public function addConnection(ConnectionInterface $conn): void
    {
        $this->connectionsById[$conn->resourceId] = $conn;
    }

    public function add(string $sessionId, ConnectionInterface $conn): void
    {
        $this->connections[$sessionId][$conn->resourceId] = $conn;
        $this->connectionsById[$conn->resourceId] = $conn;
    }

    public function remove(ConnectionInterface $conn): void
    {
        $resourceId = $conn->resourceId;

        if (isset($this->idToTokens[$resourceId])) {
            foreach ($this->idToTokens[$resourceId] as $playerToken) {
                unset($this->tokenToId[$playerToken]);
            }
            unset($this->idToTokens[$resourceId]);
        }

        unset($this->connectionsById[$resourceId]);

        foreach ($this->connections as $sessionId => $clients) {
            if (isset($clients[$resourceId])) {
                unset($this->connections[$sessionId][$resourceId]);
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

    public function bindToken(string $playerToken, ConnectionInterface $conn): void
    {
        $resourceId = $conn->resourceId;
        $this->connectionsById[$resourceId] = $conn;
        $this->tokenToId[$playerToken] = $resourceId;
        $this->idToTokens[$resourceId] ??= [];
        $this->idToTokens[$resourceId][$playerToken] = $playerToken;
    }

    public function getByToken(string $playerToken): ?ConnectionInterface
    {
        $resourceId = $this->tokenToId[$playerToken] ?? null;

        return $resourceId !== null ? ($this->connectionsById[$resourceId] ?? null) : null;
    }

    public function sendToToken(string $playerToken, array $message): void
    {
        $conn = $this->getByToken($playerToken);

        if ($conn === null) {
            return;
        }

        $conn->send(json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function getTokenByConnection(ConnectionInterface $conn): ?string
    {
        $resourceId = $conn->resourceId;
        if (!isset($this->idToTokens[$resourceId])) {
            return null;
        }

        return array_key_first($this->idToTokens[$resourceId]);
    }
}
