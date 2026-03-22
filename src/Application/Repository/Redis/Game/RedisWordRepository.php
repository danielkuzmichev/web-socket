<?php

namespace App\Application\Repository\Redis\Game;

use App\Game\Repository\WordRepositoryInterface;
use App\Redis\RedisClient;
use App\Redis\RedisRepository;

class RedisWordRepository extends RedisRepository implements WordRepositoryInterface
{
    public function __construct(private RedisClient $redis, int $dbIndex = 0)
    {
        parent::__construct($redis, $dbIndex);
    }

    public function exists(string $word): bool
    {
        $firstLetter = mb_substr(mb_strtolower($word), 0, 1);
        return $this->redis->exists("words:$firstLetter", mb_strtolower($word));
    }

    public function getRandomSessionWord(): string
    {
        return $this->redis->getClient()->srandmember('words:long');
    }
}
