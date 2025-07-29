<?php

namespace App\Domain\Session\Event;

use App\Core\Entity\EntityInterface;

class StartSession implements EntityInterface
{
    public function __construct(private string $sessionId)
    {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}