<?php

namespace App\Domain\Game\Entity;

class Game
{
    public function __construct(
        private string $id,
        private string $word,
        private string $summaryType,
        private array $players
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

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'word' => $this->word,
            'summary_type' => $this->summaryType,
            'players' => array_map(fn($player) => $player->toArray(), $this->players)
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $data): self
    {
         $players = array_map(
            fn($playerData) => Player::fromArray($playerData),
            $data['players'] ?? []
        );

        return new self(
            $data['id'],
            $data['word'],
            $data['summary_type'],
            $players
        );
    }

    public function setPlayerByKey(string $id, Player $player): self
    {
        $this->players[$id] = $player;

        return $this;
    }

    public function getPlayerByKey(string $id): ?Player
    {
        return $this->players[$id];
    }

    public function removePlayerById(string $id): self
    {
        unset($this->players[$id]);

        return $this;
    }
}