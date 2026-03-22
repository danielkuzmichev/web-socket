<?php

namespace App\Application\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('create_game')]
class CreateGame implements EventInterface
{
    public function __construct(
        private string $summaryType,
        private string $playerToken,
        private int $countOfConnections = 2,
    ) {
    }

    public function getSummaryType()
    {
        return $this->summaryType;
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
