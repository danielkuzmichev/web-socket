<?php

namespace App\Application\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('send_word')]
class SendWord implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $word,
        private string $playerToken
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function getPlayerToken(): string
    {
        return $this->playerToken;
    }
}
