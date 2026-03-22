<?php

namespace App\Session\Repository;

use App\Session\Entity\Session;
use Ratchet\ConnectionInterface;

interface SessionRepositoryInterface
{
    public function create(Session $session): void;

    public function find(string $sessionId): ?Session;

    public function all(): array;

    public function delete(string $sessionId): void;

    public function findByConnection(ConnectionInterface $conn): mixed;

    public function add(string $sessionId, array $players, ?ConnectionInterface $conn = null): void;

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void;

    public function getPlayerTokenByConnection(ConnectionInterface $conn): ?string;

    public function save(Session $session): void;

    public function findByPlayerToken(string $playerToken): ?string;
}
