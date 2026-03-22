<?php

namespace App\Session\Service;

use App\Session\Entity\Session;
use DateTime;
use Ratchet\ConnectionInterface;

interface SessionServiceInterface
{
    public function find(string $sessionId): ?Session;

    public function createSession(string $processId, int $countOfConnections): mixed;

    public function joinToSession(string $playerToken, string $sessionId, ?ConnectionInterface $conn = null): void;

    public function setStart(string $sessionId, ?DateTime $time = null): Session;

    public function delete(string $sessionId): void;

    public function findByConnection($conn): mixed;
    /** @todo уйти от ConnectionInterface */
    public function removeConnection(string $sessionId, ConnectionInterface $conn): void;

    public function findByPlayerToken(string $playerToken): ?string;

    public function existByPlayerToken(string $playerToken): bool;
}
