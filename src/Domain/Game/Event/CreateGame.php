<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('create_game')]
class CreateGame implements EventInterface
{
    public function __construct(
        private string $summaryType,
        private int $countOfConnections
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
}