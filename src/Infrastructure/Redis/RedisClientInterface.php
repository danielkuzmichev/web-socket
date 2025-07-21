<?php

namespace App\Infrastructure\Redis;

interface RedisClientInterface
{
    public function connect(string $host, int $port): void;
    public function get(string $key);
    public function set(string $key, string $value);
    public function del(string $key): int;
    public function keys(string $keysPattern): mixed;
    public function getClient(): \Redis;
}
