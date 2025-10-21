<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('player_joined')]
class PlayerJoined implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $gameId,
        private string $connectionId,
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

    public function getConnectionId()
    {
        return $this->connectionId;
    }
}