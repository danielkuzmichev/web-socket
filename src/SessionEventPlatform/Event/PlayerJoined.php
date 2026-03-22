<?php

namespace App\SessionEventPlatform\Event;

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

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function getPlayerToken()
    {
        return $this->playerToken;
    }
}