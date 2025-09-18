<?php

namespace App\Infrastructure\Repository\Redis\Game;

use App\Domain\Game\Entity\Game;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Infrastructure\Redis\RedisClient;
use App\Infrastructure\Repository\Redis\RedisRepository;

class RedisWordRepository extends RedisRepository implements GameRepositoryInterface
{
    public function __construct(private RedisClient $redis, int $dbIndex = 0)
    {
        parent::__construct($redis, $dbIndex);
    }

    public function save(Game $game): void
    {
        $gameId = $game->getId();
        $this->redis->set("game:$gameId", $game->toJson());
    }
}