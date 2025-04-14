<?php

namespace App\Handler;

use App\Repository\GameSessionRepositoryInterface;
use App\Repository\InMemoryGameSessionRepository;
use Ratchet\ConnectionInterface;

class CreateSessionHandler implements MessageHandlerInterface {

    private GameSessionRepositoryInterface $gameSessionRepository;

    public function __construct() {
        $this->gameSessionRepository = InMemoryGameSessionRepository::getInstance();
    }

    public function getType(): string {
        return 'create_session';
    }

    public function handle(array $payload, ConnectionInterface $conn): void {
        if ($this->gameSessionRepository->findByConnection($conn)) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'You already created or joined a session.']
            ]));
            return;
        }

        $sessionId = uniqid('session_', true);
        $this->gameSessionRepository->add($sessionId, [$conn]);

        $conn->send(json_encode([
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId' => $sessionId
            ]
        ]));
    }
}
