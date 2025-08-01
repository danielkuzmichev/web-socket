<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('join_session')]
class JoinSession implements EventInterface
{
    public function __construct(private string $sessionId)
    {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
