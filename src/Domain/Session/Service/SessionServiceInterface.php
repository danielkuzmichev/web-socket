<?php

namespace App\Domain\Session\Service;

use DateTime;

interface SessionServiceInterface
{
    public function createSession($player, $options): mixed;

    public function joinToSession($player, string $sessionId): void;

    public function setStart(string $sessionId, ?DateTime $time = null): array;

    public function delete(string $sessionId): void;
}
