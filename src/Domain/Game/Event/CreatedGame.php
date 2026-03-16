<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('game_created')]
class CreatedGame implements EventInterface
{
    public function __construct(
        private string $gameId,
        private int $countOfConnections,
        private string $playerToken
    ) {
    }

    public function getGameId(): string
    {
        return $this->gameId;
    }

    public function getCountOfConnections(): int
    {
        return $this->countOfConnections;
    }

    public function getPlayerToken(): string
    {
        return $this->playerToken;
    }
}
