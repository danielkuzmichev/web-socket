<?php

namespace App\Infrastructure\Repository\Word;

use App\Util\Redis\RedisClient;

class RedisWordRepository implements WordRepositoryInterface
{
    public function __construct(private RedisClient $redis)
    {
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
