<?php

namespace App\SessionEventPlatform\Handler;

use App\Session\Service\SessionServiceInterface;
use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\SessionEventPlatform\Event\JoinSession;
use App\SessionEventPlatform\Event\SessionJoined;
use App\SessionEventPlatform\Event\StartSession;
use App\Session\Repository\SessionRepositoryInterface;
use App\Connection\ConnectionStorageInterface;

class JoinSessionHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private SessionRepositoryInterface $sessionRepository,
        private ?WebSocketDispatcherInterface $dispatcher,
        private ConnectionStorageInterface $connectionStorage,
    ) {
    }

    public function getEventClass(): string
    {
        return JoinSession::class;
    }

    public function process(EventInterface $event): void
    {
        /** @var JoinSession $event */
        $playerToken = $event->getPlayerToken();
        $sessionId = $event->getSessionId() ?? null;

        $this->sessionService->joinToSession($playerToken, $sessionId);

        $session = $this->sessionRepository->find($sessionId);

        $this->dispatcher->dispatch(
            new SessionJoined(
                $sessionId,
                $session->getProcessId(),
                $playerToken
            )
        );

        $this->connectionStorage->sendToToken($playerToken, [
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId,
            ]
        ]);

        if (count($session->getConnections()) === $session->getCountOfConnections()) {
            $this->startCountdown($sessionId);
        }
    }

    private function startCountdown(string $sessionId): void
    {
        $this->dispatcher->dispatch(new StartSession($sessionId));
    }
}
