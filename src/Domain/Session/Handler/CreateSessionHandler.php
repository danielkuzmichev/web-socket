<?php

namespace App\Domain\Session\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Session\Event\CreateSession;
use App\Domain\Session\Event\CreateSessionFail;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Infrastructure\Connection\ConnectionStorageInterface;
use App\Util\Exception\DuplicateException;
use Ratchet\ConnectionInterface;
use Throwable;

class CreateSessionHandler extends AbstractEventHandler
{
    public function __construct( 
        private SessionServiceInterface $sessionService,
        private ConnectionStorageInterface $connectionStorage,
        private ?WebSocketDispatcherInterface $dispatcher,
    ) {
    }

    public function getEventClass(): string
    {
        return CreateSession::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        if ($this->sessionService->findByConnection($conn)) {
            throw new DuplicateException('You already created or joined a session.');
        }

        try {
            /** @var CreateSession $event */
            $processId = $event->getProcessId();
            $session = $this->sessionService->createSession($processId, $event->getCountOfConnections());
            $sessionId = $session->getId();
            $this->sessionService->joinToSession($conn, $sessionId);
            $this->connectionStorage->add($sessionId, $conn);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch(new CreateSessionFail($processId));
            throw $e;
        }
        $conn?->send(json_encode([
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId' => $sessionId,
            ]
        ]));
    }
}
