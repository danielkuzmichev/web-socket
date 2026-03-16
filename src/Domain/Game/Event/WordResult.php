<?php

namespace App\Domain\Game\Event;

use App\Core\Attribute\Event;
use App\Core\Event\EventInterface;

#[Event('word_result')]
class WordResult implements EventInterface
{
    public function __construct(
        private string $sessionId,
        private string $playerToken,
        private string $message,
        private ?int $score,
        private ?int $totalScore
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

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function getTotalScore(): ?int
    {
        return $this->totalScore;
    }
}
