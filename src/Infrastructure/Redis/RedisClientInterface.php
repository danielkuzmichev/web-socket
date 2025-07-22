<?php

namespace App\Infrastructure\Redis;

interface RedisClientInterface
{
    public function connect(string $host, int $port, int $dbIndex = 0): void;
    public function selectDb(int $dbIndex): void;
    public function get(string $key);
    public function set(string $key, string $value, ?int $ttl = null): bool;
    public function del(string $key): int;
    public function keys(string $keysPattern): mixed;
    public function getClient(): \Redis;
}
