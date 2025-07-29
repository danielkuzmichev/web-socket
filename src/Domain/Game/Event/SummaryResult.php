<?php

namespace App\Domain\Game\Event;

use App\Core\Event\EventInterface;

class SummaryResult implements EventInterface
{
    public function __construct(
        private string $sessionId
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}