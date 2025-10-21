<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('summary_result')]
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
