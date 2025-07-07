<?php

namespace App\Application\Session\Service;

use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\DuplicateException;
use App\Util\Exception\InvalidDataException;
use App\Util\Exception\NotFoundException;
use DateTime;

class SessionService implements SessionServiceInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WordRepositoryInterface $wordRepository,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function createSession($player, $options): mixed
    {
        if ($this->sessionRepository->findByConnection($player)) {
            throw new DuplicateException('You already created or joined a session.');
        }

        $sessionId = 1; // uniqid(more_entropy: true);
        if (!isset($payload['summary_type']) && $options['summary_type'] == null) {
            throw new InvalidDataException('Not found summary_type');
        }

        $sessionWord = 'приветствие';//$this->wordRepository->getRandomSessionWord();

        $session = [
            'sessionId' => $sessionId,
            'summaryType' => $options['summary_type'],
            'sessionWord' => $sessionWord,
        ];

        $this->sessionRepository->create($session);
        $this->sessionRepository->add($sessionId, [$player]);

        // Добавляем соединение в ConnectionStorage
        $this->connectionStorage->add($sessionId, $player);

        return ['sessionId' => $sessionId, 'sessionWord' => $sessionWord];
    }

    public function joinToSession($player, string $sessionId): void
    {
        $session = $this->sessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        if (count($session['players']) >= 2) {
            throw new DomainLogicalException('Session is full.');
        }

        if ($this->sessionRepository->findByConnection($player)) {
            throw new DomainLogicalException('You already joined or created a session.');
        }

        // Теперь ДЕЙСТВИТЕЛЬНО добавляем соединение в сессию
        $this->sessionRepository->add($sessionId, [$player]);

        // И в ConnectionStorage
        $this->connectionStorage->add($sessionId, $player);
    }

    public function setStart(string $sessionId, ?DateTime $time = null): array
    {
        $session = $this->sessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found');
        }

        if (empty($session['players'])) {
            throw new DomainLogicalException('Cannot start empty session');
        }

        $startAt = $time
            ? $this->validateFutureTime($time)
            : microtime(true);

        $session['startAt'] = $startAt;
        $this->sessionRepository->save($session);

        return $session;
    }

    private function validateFutureTime(DateTime $time): float
    {
        if (new DateTime() >= $time) {
            throw new InvalidDataException(
                sprintf('Start time must be in future (%s)', $time->format('d.m.Y H:i:s'))
            );
        }
        return (float)$time->format('U.u');
    }

    public function delete(string $sessionId): void
    {
        $this->sessionRepository->delete($sessionId);
    }
}
