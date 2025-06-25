<?php

namespace App\Application\Session\Service;

use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\DuplicateException;
use App\Util\Exception\InvalidDataException;
use App\Util\Exception\NotFoundException;

class SessionService implements SessionServiceInterface
{
    public function __construct(
        private GameSessionRepositoryInterface $gameSessionRepository,
        private WordRepositoryInterface $wordRepository,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function createSession($player, $options): mixed
    {
        if ($this->gameSessionRepository->findByConnection($player)) {
            throw new DuplicateException('You already created or joined a session.');
        }

        $sessionId = uniqid(more_entropy: true);
        if (!isset($payload['summary_type']) && $options['summary_type'] == null) {
            throw new InvalidDataException('Not found summary_type');
        }

        $sessionWord = $this->wordRepository->getRandomSessionWord();

        $session = [
            'sessionId' => $sessionId,
            'summaryType' => $options['summary_type'],
            'sessionWord' => $sessionWord,
        ];

        $this->gameSessionRepository->create($session);
        $this->gameSessionRepository->add($sessionId, [$player]);

        // Добавляем соединение в ConnectionStorage
        $this->connectionStorage->add($sessionId, $player);

        return ['sessionId' => $sessionId, 'sessionWord' => $sessionWord];
    }

    public function joinToSession($player, string $sessionId): void
    {
        $session = $this->gameSessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        if (count($session['players']) >= 2) {
            throw new DomainLogicalException('Session is full.');
        }

        if ($this->gameSessionRepository->findByConnection($player)) {
            throw new DomainLogicalException('You already joined or created a session.');
        }

        // Теперь ДЕЙСТВИТЕЛЬНО добавляем соединение в сессию
        $this->gameSessionRepository->add($sessionId, [$player]);

        // И в ConnectionStorage
        $this->connectionStorage->add($sessionId, $player);
    }
}
