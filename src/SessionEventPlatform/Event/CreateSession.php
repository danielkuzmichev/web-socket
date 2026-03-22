<?php

namespace App\SessionEventPlatform\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('create_session')]
class CreateSession implements EventInterface
{
    public function __construct(
        private string $processId,
        private int $countOfConnections,
        private string $playerToken
    ) {
    }

    public function getProcessId(): string
    {
        return $this->processId;
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
