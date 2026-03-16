<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('word_rejected')]
class WordRejected implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $playerToken,
        private string $message
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getPlayerToken(): string
    {
        return $this->playerToken;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
