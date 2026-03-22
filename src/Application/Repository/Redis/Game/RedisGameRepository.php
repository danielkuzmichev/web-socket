<?php

namespace App\Application\Repository\Redis\Game;

use App\Game\Entity\Game;
use App\Game\Repository\GameRepositoryInterface;
use App\Redis\RedisClient;
use App\Redis\RedisRepository;

class RedisGameRepository extends RedisRepository implements GameRepositoryInterface
{
    public function __construct(private RedisClient $redis, int $dbIndex = 0)
    {
        parent::__construct($redis, $dbIndex);
    }

    public function save(Game $game): void
    {
        $gameId = $game->getId();
        $this->redis->set("game_session:$gameId", $game->toJson());
    }

    public function find(string $id): ?Game
    {
        $data = $this->redis->get("game_session:$id");

        return $data ? Game::fromArray(json_decode($data, true)) : null;
    }
}