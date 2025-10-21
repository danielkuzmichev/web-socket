<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('session_started')]
class SessionStarted implements EventInterface
{
    public function __construct(
        private string $sessionId,
    ) {
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }
}