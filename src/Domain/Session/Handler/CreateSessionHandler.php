<?php

namespace App\Domain\Session\Handler;

use App\Domain\Session\Service\SessionServiceInterface;
use App\Core\Handler\EventHandlerInterface;
use Ratchet\ConnectionInterface;

class CreateSessionHandler implements EventHandlerInterface
{
    public function __construct(private SessionServiceInterface $sessionService)
    {
    }

    public function getType(): string
    {
        return 'create_session';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $session = $this->sessionService->createSession($conn, $payload);

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
