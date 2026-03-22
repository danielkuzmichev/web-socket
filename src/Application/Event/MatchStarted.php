<?php

namespace App\Application\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('match_started')]
class MatchStarted implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $targetWord
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getTargetWord(): string
    {
        return $this->targetWord;
    }
}
