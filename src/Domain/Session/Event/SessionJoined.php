<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('session_joined')]
class SessionJoined implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $processId,
        private string $playerToken
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getPlayerToken(): string
    {
        return $this->playerToken;
    }
}
