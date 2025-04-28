<?php

namespace App\Infrastructure\Repository\GameSession;

use Ratchet\ConnectionInterface;

interface GameSessionRepositoryInterface
{
    public function create(mixed $session): void;

    public function find(string $sessionId): mixed;

    public function all(): array;

    public function delete(string $sessionId): void;

    public function findByConnection(ConnectionInterface $conn): mixed;

    public function add(string $sessionId, array $players): void;

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void;
}
