<?php

namespace App\Infrastructure\Repository\Redis\Game;

use App\Domain\Game\Repository\WordRepositoryInterface;
use App\Infrastructure\Redis\RedisClient;
use App\Infrastructure\Repository\Redis\RedisRepository;

class RedisWordRepository extends RedisRepository implements WordRepositoryInterface
{
    public function __construct(private RedisClient $redis, int $dbIndex = 0)
    {
        parent::__construct($redis, $dbIndex);
    }

    public function exists(string $word, string $lang): bool
    {
        $firstLetter = mb_substr(mb_strtolower($word), 0, 1);
        return $this->redis->exists("words:$lang:$firstLetter", mb_strtolower($word));
    }

    public function getRandomSessionWord(string $lang): string
    {
        return $this->redis->getClient()->srandmember("words:$lang:long");
    }
}
