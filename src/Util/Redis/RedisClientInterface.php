<?php

namespace App\Util\Redis;

interface RedisClientInterface
{
    public function connect(string $host, int $port): bool;
    public function get(string $key);
    public function set(string $key, string $value);
    public function del(string $key): int;
    public function keys(string $keysPattern): mixed;
}
