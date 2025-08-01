<?php

namespace App\Domain\Session\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Session\Event\CreateSession;
use App\Domain\Session\Service\SessionServiceInterface;
use Ratchet\ConnectionInterface;

class CreateSessionHandler extends AbstractEventHandler
{
    public function __construct(private SessionServiceInterface $sessionService)
    {
    }

    public function getEventClass(): string
    {
        return CreateSession::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var CreateSession $event */
        $session = $this->sessionService->createSession($conn, ['summary_type' => $event->getSummaryType()]);

        $conn->send(json_encode([
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId' => $session['sessionId'],
                'sessionWord' => $session['sessionWord'],
            ]
        ]));
    }
}
