<?php

namespace App\Domain\Game\Entity;

class Game
{
    public function __construct(
        private string $id,
        private string $word,
        private string $summaryType,
        private array $player
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function getSummaryType(): string
    {
        return $this->summaryType;
    }

    public function getPlayer(): array
    {
        return $this->player;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'word' => $this->word,
            'summary_type' => $this->summaryType,
            'player' => $this->player
        ];
    }

    public function toJson(): string
    {
        return json_encode([
            'id' => $this->id,
            'word' => $this->word,
            'summary_type' => $this->summaryType,
            'player' => $this->player
        ], JSON_THROW_ON_ERROR);
    }
}