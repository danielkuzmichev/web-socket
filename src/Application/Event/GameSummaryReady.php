<?php

namespace App\Application\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('game_summary_ready')]
class GameSummaryReady implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private array $results
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
