<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('send_word')]
class SendWord implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $word
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
}
