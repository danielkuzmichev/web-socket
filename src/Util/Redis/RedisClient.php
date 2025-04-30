<?php

namespace App\Util\Redis;

class RedisClient implements RedisClientInterface
{
    private \Redis $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
    }

    public function connect(string $host, int $port): bool
    {
        return $this->redis->connect($host, $port);
    }

    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    public function set(string $key, string $value)
    {
        return $this->redis->set($key, $value);
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
}
