<?php

namespace App\Domain\Session\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('create_session')]
class CreateSession implements EventInterface
{
    public function __construct(private string $summaryType)
    {
    }

    public function getSummaryType(): string
    {
        return $this->summaryType;
    }
}
