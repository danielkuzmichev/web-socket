<?php

namespace App\Handler;

use App\Dispatcher\MessageDispatcherInterface;
use App\Repository\GameSessionRepositoryInterface;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface {

    private GameSessionRepositoryInterface $gameSessionRepository;
    private ?MessageDispatcherInterface $dispatcher;

    public function __construct(
        GameSessionRepositoryInterface $gameSessionRepository,
        ?MessageDispatcherInterface $dispatcher
    ) {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->dispatcher = $dispatcher;
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

        if ($this->gameSessionRepository->findByConnection($conn)) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'You already joined or created a session.']
            ]));
            return;
        }

        $session['players'][] = $conn;
        $this->gameSessionRepository->add($sessionId, $session['players']);

        $conn->send(json_encode([
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId
            ]
        ]));

        if (count($session['players']) === 2) {
            foreach ($session['players'] as $playerConn) {
                $this->dispatcher->dispatchFromArray([
                    'type' => 'countdown_start',
                    'payload' => [
                        'seconds' => 3,
                        'sessionId' => $sessionId
                    ]
                ], $playerConn);
            }
        }
    }
}
