<?php

namespace App\SessionEventPlatform\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\SessionEventPlatform\Event\FinishSession;
use App\Session\Service\SessionServiceInterface;
use App\Session\Service\TimerService;
use App\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;

class FinishSessionHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private TimerService $timerService,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return FinishSession::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var FinishSession $event */
        $sessionId = $event->getSessionId();

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
