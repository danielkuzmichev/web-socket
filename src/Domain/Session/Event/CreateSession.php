<?php

namespace App\Domain\Session\Event;

use App\Core\Entity\EntityInterface;

class CreateSession implements EntityInterface
{
    public function __construct(private string $summaryType) 
    {
    }

    public function getSummaryType(): string
    {
        return $this->summaryType;
    }
}