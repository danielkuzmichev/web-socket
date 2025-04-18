<?php

namespace App\Repository;

use App\Entity\GameSession;
use Ratchet\ConnectionInterface;

class InMemoryGameSessionRepository implements GameSessionRepositoryInterface
{
    private static ?self $instance = null;

    /** @var GameSession[] */
    private array $sessions = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new InMemoryGameSessionRepository();
        }

        return self::$instance;
    }

    public function create(mixed $session): void
    {
        $this->sessions[$session->getId()] = $session;
    }

    public function find(string $sessionId): mixed
    {
        return $this->sessions[$sessionId] ?? null;
    }

    public function all(): array
    {
        return $this->sessions;
    }

    public function delete(string $sessionId): void
    {
        unset($this->sessions[$sessionId]);
    }

    public function findByConnection(ConnectionInterface $conn): ?string
    {
        foreach ($this->sessions as $sessionId => $session) {
            foreach ($session['players'] as $player) {
                if ($player === $conn) {
                    return $sessionId;
                }
            }
        }
        return null;
    }

    public function add(string $sessionId, array $players): void
    {
        $this->sessions[$sessionId] = ['players' => $players];
    }

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void
    {
        if (isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId]['players'] = array_filter(
                $this->sessions[$sessionId]['players'],
                fn($player) => $player !== $conn
            );

            if (empty($this->sessions[$sessionId]['players'])) {
                unset($this->sessions[$sessionId]);
            }
        }
    }
}
