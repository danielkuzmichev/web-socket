<?php

namespace App\Application\Session\Service;

interface SessionServiceInterface
{
    public function createSession($player, $options): mixed;

    public function joinToSession($player, string $sessionId): void;
}
