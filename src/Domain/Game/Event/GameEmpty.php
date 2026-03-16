<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('game_empty')]
class GameEmpty implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $gameId
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getGameId(): string
    {
        return $this->gameId;
    }
}
