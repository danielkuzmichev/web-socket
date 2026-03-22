<?php

namespace App\Infrastructure\Repository\Redis\Session;

use App\Domain\Session\Entity\Session;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Redis\RedisClientInterface;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class RedisSessionRepository implements SessionRepositoryInterface
{
    private RedisClientInterface $redis;

    public function __construct(RedisClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function create(Session $session): void
    {
        $sessionId = $session->getId();
        $this->redis->set("session:$sessionId", $session->toJson());
    }

    public function find(string $sessionId): ?Session
    {
        $data = $this->redis->get("session:$sessionId");
        return $data ? Session::fromArray(json_decode($data, true)) : null;
    }

    public function all(): array
    {
        $keys = $this->redis->keys("session:*");
        $sessions = [];
        foreach ($keys as $key) {
            $data = $this->redis->get($key);
            if ($data) {
                $sessions[] = Session::fromArray(json_decode($data, true));
            }
        }
        return $sessions;
    }

    public function delete(string $sessionId): void
    {
        $session = $this->find($sessionId);
        if ($session) {
            $playerTokens = array_values($session->getConnections());

            foreach ($playerTokens as $playerToken) {
                $this->redis->del("connection_to_session:{$playerToken}");
            }

            $resourceToTokenKeys = $this->redis->keys('connection_resource_to_token:*') ?: [];
            foreach ($resourceToTokenKeys as $resourceKey) {
                $mappedToken = $this->redis->get($resourceKey);
                if ($mappedToken !== false && in_array($mappedToken, $playerTokens, true)) {
                    $this->redis->del($resourceKey);
                }
            }
        }
        $this->redis->del("session:$sessionId");
    }

    public function findByConnection(ConnectionInterface $conn): mixed
    {
        /** @todo уйти от сущности ConnectionInterface */
        $playerToken = $this->redis->get("connection_resource_to_token:{$conn->resourceId}");
        if ($playerToken) {
            $sessionId = $this->redis->get("connection_to_session:$playerToken");
            if ($sessionId) {
                return $sessionId;
            }
        }

        // Fallback for legacy mapping where resourceId was used directly
        $legacySessionId = $this->redis->get("connection_to_session:{$conn->resourceId}");

        return $legacySessionId ?: null;
    }

    public function findByPlayerToken(string $playerToken): ?string
    {
        $sessionId = $this->redis->get("connection_to_session:$playerToken");
        if (!$sessionId) {
            return null;
        }
        return $sessionId;
    }

    public function add(string $sessionId, array $players, ?ConnectionInterface $conn = null): void
    {
        $session = $this->find($sessionId);
        if (!$session) {
            throw new NotFoundException("Session not found");
        }

        foreach ($players as $playerToken) {
            $session->addConnection($playerToken);
            $this->redis->set("connection_to_session:{$playerToken}", $sessionId);
            if ($conn !== null) {
                $this->redis->set("connection_resource_to_token:{$conn->resourceId}", $playerToken);
            }
        }
        $this->save($session);
    }

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void
    {
        $session = $this->find($sessionId);

        if (!$session) {
            return;
        }

        $playerToken = $this->redis->get("connection_resource_to_token:{$conn->resourceId}");

        if ($playerToken !== null) {
            $session->removeConnection($playerToken);
        } else {
            // Fallback cleanup if token not found
            $session->removeConnection($conn->resourceId);
        }

        $conns = $session->getConnections();

        // Обновляем сессию в Redis
        if (!empty($conns)) {
            $this->save($session);
        } else {
            // Если больше нет игроков, удаляем сессию полностью
            $this->redis->del("session:$sessionId");
        }

        // Удаляем привязки connection -> playerToken и playerToken -> session
        if ($playerToken !== null) {
            $this->redis->del("connection_to_session:{$playerToken}");
        }

        // Clean up possible legacy mapping and resource->token mapping
        $this->redis->del("connection_to_session:{$conn->resourceId}");
        $this->redis->del("connection_resource_to_token:{$conn->resourceId}");
    }

    public function getPlayerTokenByConnection(ConnectionInterface $conn): ?string
    {
        $playerToken = $this->redis->get("connection_resource_to_token:{$conn->resourceId}");

        return $playerToken ?: null;
    }

    public function save(Session $session): void
    {
        $sessionId = $session->getId();
        $this->redis->set("session:$sessionId", $session->toJson());
    }
}
