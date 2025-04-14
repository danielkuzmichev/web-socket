<?php

namespace App\Handler;

use App\Repository\GameSessionRepositoryInterface;
use App\Repository\InMemoryGameSessionRepository;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface {

    private GameSessionRepositoryInterface $gameSessionRepository;

    public function __construct() {
        $this->gameSessionRepository = InMemoryGameSessionRepository::getInstance();
    }

    public function getType(): string {
        return 'join_session';
    }

    public function handle(array $payload, ConnectionInterface $conn): void {
        $sessionId = $payload['sessionId'] ?? null;

        if (!$sessionId) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Missing session ID.']
            ]));
            return;
        }

        $session = $this->gameSessionRepository->find($sessionId);

        if (!$session) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Session not found.']
            ]));
            return;
        }

        if (count($session['players']) >= 2) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Session is full.']
            ]));
            return;
        }

        // Проверка: не состоит ли игрок уже в другой сессии
        if ($this->gameSessionRepository->findByConnection($conn)) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'You already joined or created a session.']
            ]));
            return;
        }

        $session['players'][] = $conn;
        $this->gameSessionRepository->add($sessionId, $session['players']); // перезапись

        $conn->send(json_encode([
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId
            ]
        ]));
    }
}
