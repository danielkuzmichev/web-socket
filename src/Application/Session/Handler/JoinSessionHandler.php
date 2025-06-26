<?php

namespace App\Application\Session\Handler;

use App\Application\Session\Service\SessionServiceInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private GameSessionRepositoryInterface $gameSessionRepository,
        private ?MessageDispatcherInterface $dispatcher,
    ) {
    }

    public function getType(): string
    {
        return 'join_session';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $sessionId = $payload['sessionId'] ?? null;

        if (!$sessionId) {
            throw new InvalidDataException('Missing session ID.');
        }

        $this->sessionService->joinToSession($conn, $sessionId);

        $session = $this->gameSessionRepository->find($sessionId);

        $conn->send(json_encode([
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId,
                'sessionWord' => $session['sessionWord'],
            ]
        ]));

        if (count($session['players']) === 2) {
            $this->startCountdown($sessionId);
        }
    }

    private function startCountdown(string $sessionId): void
    {
        var_dump(1234);
        $this->dispatcher->dispatchFromArray([
            'type' => 'start_session',
            'payload' => [
                'sessionId' => $sessionId
            ]
        ]);
    }
}
