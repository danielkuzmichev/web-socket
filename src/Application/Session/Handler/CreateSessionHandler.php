<?php

namespace App\Application\Session\Handler;

use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;

class CreateSessionHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;
    private ConnectionStorage $connectionStorage;
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        GameSessionRepositoryInterface $gameSessionRepository,
        WordRepositoryInterface $wordRepository,
        ConnectionStorage $connectionStorage
    ) {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->connectionStorage = $connectionStorage;
        $this->wordRepository = $wordRepository;
    }

    public function getType(): string
    {
        return 'create_session';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        if ($this->gameSessionRepository->findByConnection($conn)) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'You already created or joined a session.']
            ]));
            return;
        }

        $sessionId = uniqid('session_', true);
        if (!isset($payload['summary_type']) && $payload['summary_type'] == null) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Not found summary_type']
            ]));
            return;
        }

        $sessionWord = $this->wordRepository->getRandomSessionWord();

        $session = [
            'sessionId' => $sessionId,
            'summaryType' => $payload['summary_type'],
            'sessionWord' => $sessionWord,
        ];

        $this->gameSessionRepository->create($session);
        $this->gameSessionRepository->add($sessionId, [$conn]);

        // Добавляем соединение в ConnectionStorage
        $this->connectionStorage->add($sessionId, $conn);

        $conn->send(json_encode([
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId' => $sessionId,
                'sessionWord' => $sessionWord,
            ]
        ]));
    }
}
