<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('game_player_joined')]
class GamePlayerJoined implements EventInterface
{
    public function __construct(
        private string $gameId,
        private string $playerToken
    ) {
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
