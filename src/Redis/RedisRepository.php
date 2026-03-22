<?php

namespace App\Redis;

use App\Redis\RedisClient;

class RedisRepository
{
    protected RedisClient $client;
    protected int $dbIndex;

    public function __construct(RedisClient $client, int $dbIndex = 0)
    {
        $this->client = $client;
        $this->client->selectDb($dbIndex);
    }
}
