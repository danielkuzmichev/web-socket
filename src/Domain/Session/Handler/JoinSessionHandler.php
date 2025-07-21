<?php

namespace App\Domain\Session\Handler;

use App\Domain\Session\Service\SessionServiceInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private SessionRepositoryInterface $sessionRepository,
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

        $session = $this->sessionRepository->find($sessionId);

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
        $this->dispatcher->dispatchFromArray([
            'type' => 'start_session',
            'payload' => [
                'sessionId' => $sessionId
            ]
        ]);
    }
}
