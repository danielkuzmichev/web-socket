<?php

namespace App\Application\Session\Handler;

use App\Application\Session\Service\SessionServiceInterface;
use App\Core\Handler\MessageHandlerInterface;
use Ratchet\ConnectionInterface;

class CreateSessionHandler implements MessageHandlerInterface
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
