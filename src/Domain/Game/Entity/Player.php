<?php

namespace App\Domain\Game\Entity;

class Player
{
    public function __construct(
        private string $id,
        private array $words = [],
        private float $score = 0
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore($score): self
    {
        $this->score = $score;

        return $this;
    }

    public function addScore(float $score): self
    {
        $this->score += $score;

        return $this;
    }

    public function getWords(): array
    {
        return $this->words;
    }

    public function setWords(array $words): self
    {
        $this->words = $words;

        return $this;
    }

    public function addWord(string $word): self
    {
        $this->words[] = $word;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'words' => $this->words,
            'score' => $this->score,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['words'],
            $data['score'],
        );
    }
}