<?php

namespace App\Domain\Session\Handler;

use App\Domain\Session\Service\SessionServiceInterface;
use App\Domain\Session\Service\TimerService;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class FinishSessionHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private TimerService $timerService,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function getType(): string
    {
        return 'finish_session';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $sessionId = $payload['sessionId'];
        if (!$sessionId) {
            throw new InvalidDataException('No sessionId');
        }
        $this->sessionService->delete($sessionId);
        $this->timerService->cancelAll($sessionId);

        // Уведомляем всех, что матч завершён
        $this->connectionStorage->broadcastToSession($sessionId, [
            'type' => 'match_ended',
            'payload' => ['message' => 'Match ended!']
        ]);

        // Уведомляем всех, что матч завершён
        $this->connectionStorage->broadcastToSession($sessionId, [
            'type' => 'session_is_deleted',
            'payload' => ['message' => 'Session is deleted']
        ]);

        $conn?->send([
            'type' => 'session_is_deleted',
            'payload' => ['message' => 'Session is deleted']
        ]);
    }
}
