<?php

namespace App\Application\Handler\Session;

use App\Core\Handler\MessageHandlerInterface;
use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;
    private ?MessageDispatcherInterface $dispatcher = null;
    private ConnectionStorage $connectionStorage;

    public function __construct(
        GameSessionRepositoryInterface $gameSessionRepository,
        ConnectionStorage $connectionStorage,
        MessageDispatcherInterface $dispatcher
    ) {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->connectionStorage = $connectionStorage;
        $this->dispatcher = $dispatcher;
    }

    public function getType(): string
    {
        return 'join_session';
    }

    public function handle(array $payload, ConnectionInterface $conn): void
    {
        $sessionId = $payload['sessionId'] ?? null;

        if (!$sessionId) {
            $this->sendError($conn, 'Missing session ID.');
            return;
        }

        $session = $this->gameSessionRepository->find($sessionId);

        if (!$session) {
            $this->sendError($conn, 'Session not found.');
            return;
        }

        if (count($session['players']) >= 2) {
            $this->sendError($conn, 'Session is full.');
            return;
        }

        if ($this->gameSessionRepository->findByConnection($conn)) {
            $this->sendError($conn, 'You already joined or created a session.');
            return;
        }

        // Теперь ДЕЙСТВИТЕЛЬНО добавляем соединение в сессию
        $this->gameSessionRepository->add($sessionId, [$conn]);

        // И в ConnectionStorage
        $this->connectionStorage->add($sessionId, $conn);

        $conn->send(json_encode([
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId
            ]
        ]));

        // Обновляем данные о сессии (чтобы снова не делать find)
        $session = $this->gameSessionRepository->find($sessionId);

        if (count($session['players']) === 2) {
            $this->startCountdown($sessionId);
        }
    }

    private function sendError(ConnectionInterface $conn, string $message): void
    {
        $conn->send(json_encode([
            'type' => 'error',
            'payload' => ['message' => $message]
        ]));
    }

    private function startCountdown(string $sessionId): void
    {
        if (!$this->dispatcher) {
            return;
        }

        $connections = $this->connectionStorage->getConnections($sessionId);

        $startAt = microtime(true) + 5;
        $startAt = round($startAt, 3);

        foreach ($connections as $playerConn) {
            $this->dispatcher->dispatchFromArray([
                'type' => 'countdown_start',
                'payload' => [
                    'startAt' => $startAt,
                    'sessionId' => $sessionId
                ]
            ], $playerConn);
        }
    }
}