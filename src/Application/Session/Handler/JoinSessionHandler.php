<?php

namespace App\Application\Session\Handler;

use App\Core\Handler\MessageHandlerInterface;
use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\InvalidDataException;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class JoinSessionHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;
    private ?MessageDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;

    public function __construct(
        GameSessionRepositoryInterface $gameSessionRepository,
        ConnectionStorage $connectionStorage,
        MessageDispatcherInterface $dispatcher
    ) {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->connectionStorage = $connectionStorage;
        $this->dispatcher = $dispatcher;
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

        $session = $this->gameSessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        if (count($session['players']) >= 2) {
            throw new DomainLogicalException('Session is full.');
        }

        if ($this->gameSessionRepository->findByConnection($conn)) {
            throw new DomainLogicalException('You already joined or created a session.');
        }

        // Теперь ДЕЙСТВИТЕЛЬНО добавляем соединение в сессию
        $this->gameSessionRepository->add($sessionId, [$conn]);

        // И в ConnectionStorage
        $this->connectionStorage->add($sessionId, $conn);

        $conn->send(json_encode([
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId,
                'sessionWord' => $session['sessionWord'],
            ]
        ]));

        $session = $this->gameSessionRepository->find($sessionId);

        if (count($session['players']) === 2) {
            $this->startCountdown($session);
        }
    }

    private function startCountdown(mixed $session): void
    {
        $this->dispatcher->dispatchFromArray([
            'type' => 'countdown_start',
            'payload' => [
                'session' => $session
            ]
        ]);
    }
}
