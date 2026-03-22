<?php

namespace App\Application\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('player_joined')]
class PlayerJoined implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $gameId,
        private string $playerToken,
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

    public function getPlayerToken(): string
    {
        return $this->playerToken;
    }
}
