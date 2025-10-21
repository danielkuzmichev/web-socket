<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('create_session_fail')]
class CreateSessionFail implements EventInterface
{
    public function __construct(
        private string $processId,
    ) {
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }
}