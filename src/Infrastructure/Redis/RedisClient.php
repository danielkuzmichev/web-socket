<?php

namespace App\Infrastructure\Redis;

class RedisClient implements RedisClientInterface
{
    private \Redis $redis;

    private int $selectedDb = 0;

    public function __construct()
    {
        $this->redis = new \Redis();
    }

    public function connect(string $host, int $port, int $dbIndex = 0): void
    {
        $this->redis->connect($host, $port);
        $this->selectDb($dbIndex);
    }

    public function selectDb(int $dbIndex): void
    {
        $this->redis->select($dbIndex);
        $this->selectedDb = $dbIndex;
    }

    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    public function set(string $key, string $value, ?int $ttl = null): bool
    {
        return $ttl
            ? $this->redis->setex($key, $ttl, $value)
            : $this->redis->set($key, $value);
    }

    public function del(string $key): int
    {
        return $this->redis->del($key);
    }

    public function keys(string $keysPattern): mixed
    {
        return $this->redis->keys($keysPattern);
    }

    public function exists(string $key, string $value): bool
    {
        return $this->redis->sIsMember($key, $value);
    }

    public function getClient(): \Redis
    {
        return $this->redis;
    }
}
