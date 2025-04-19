<?php

namespace App\Repository;

use App\Util\Redis\RedisClientInterface;
use Ratchet\ConnectionInterface;

class RedisGameSessionRepository implements GameSessionRepositoryInterface
{
    private RedisClientInterface $redis;

    public function __construct(RedisClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function create(mixed $sessionId): void
    {
        $session = [
            'id' => $sessionId,
            'players' => [],
        ];
        $this->redis->set("game_session:$sessionId", json_encode($session));
    }

    public function find(string $sessionId): mixed
    {
        $data = $this->redis->get("game_session:$sessionId");
        return $data ? json_decode($data, true) : null;
    }

    public function all(): array
    {
        $keys = $this->redis->keys("game_session:*");
        $sessions = [];
        foreach ($keys as $key) {
            $data = $this->redis->get($key);
            if ($data) {
                $sessions[] = json_decode($data, true);
            }
        }
        return $sessions;
    }

    public function delete(string $sessionId): void
    {
        $session = $this->find($sessionId);
        if ($session) {
            foreach ($session['players'] as $player) {
                $this->redis->del("connection_to_session:{$player['connection_id']}");
            }
        }
        $this->redis->del("game_session:$sessionId");
    }

    public function findByConnection(ConnectionInterface $conn): mixed
    {
        $connectionId = $conn->resourceId;
        $sessionId = $this->redis->get("connection_to_session:$connectionId");
        if (!$sessionId) {
            return null;
        }
        return $this->find($sessionId);
    }

    public function add(string $sessionId, array $players): void
    {
        $session = $this->find($sessionId);
        if (!$session) {
            throw new \RuntimeException("Session not found");
        }
        foreach ($players as $playerConn) {
            $connId = $playerConn->resourceId;
            $session['players'][] = [
                'connection_id' => $connId,
            ];

            $this->redis->set("connection_to_session:{$connId}", $sessionId);
        }
        $this->redis->set("game_session:$sessionId", json_encode($session));
    }

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void
    {
        $session = $this->find($sessionId);

        if (!$session) {
            return;
        }

        // Убираем соединение из списка игроков
        $players = array_filter(
            $session['players'],
            fn($player) => $player['connection_id'] !== $conn->resourceId
        );

        // Обновляем сессию в Redis
        if (!empty($players)) {
            $this->redis->set("game_session:$sessionId", json_encode([
                'players' => array_values($players) // переиндексация массива
            ]));
        } else {
            // Если больше нет игроков, удаляем сессию полностью
            $this->redis->del("game_session:$sessionId");
        }

        // Удаляем привязку connection -> session
        $this->redis->del("connection_to_session:{$conn->resourceId}");
    }
}
