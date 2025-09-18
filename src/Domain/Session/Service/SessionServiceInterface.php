<?php

namespace App\Domain\Session\Service;

use App\Domain\Session\Entity\Session;
use DateTime;

interface SessionServiceInterface
{
    public function createSession(string $processId, int $countOfConnections): mixed;

    public function joinToSession($player, string $sessionId): void;

    public function setStart(string $sessionId, ?DateTime $time = null): array;

    public function delete(string $sessionId): void;

    public function findByConnection($conn): mixed;
}
