<?php

namespace App\Domain\Session\Handler;

use App\Domain\Session\Service\SessionServiceInterface;
use App\Core\Handler\EventHandlerInterface;
use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Session\Event\JoinSession;
use App\Domain\Session\Event\StartSession;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class JoinSessionHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private SessionRepositoryInterface $sessionRepository,
        private ?WebSocketDispatcherInterface $dispatcher,
    ) {
    }

    public function getEventClass(): string
    {
        return JoinSession::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var JoinSession $event */
        $sessionId = $event->getSessionId() ?? null;

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
        $this->dispatcher->dispatch(new StartSession($sessionId));
    }
}
