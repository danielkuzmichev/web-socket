<?php

namespace App\Domain\Game\Event;

use App\Core\Event\EventInterface;

class PlayerLeft implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $playerId
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getPlayerId(): string
    {
        return $this->playerId;
    }
}