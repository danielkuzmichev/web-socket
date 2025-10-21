<?php

namespace App\Domain\Session\Service;

use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Domain\Game\Repository\WordRepositoryInterface;
use App\Domain\Session\Entity\Session;
use App\Infrastructure\Connection\ConnectionStorage;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\InvalidDataException;
use App\Util\Exception\NotFoundException;
use DateTime;
use Ratchet\ConnectionInterface;

class SessionService implements SessionServiceInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WordRepositoryInterface $wordRepository,
        private ConnectionStorage $connectionStorage
    ) {}

    public function createSession(string $processId, int $countOfConnections): Session
    {
        $sessionId = uniqid(more_entropy: true);
        $session = new Session(
            $sessionId,
            $processId,
            null,
            null,
            $countOfConnections,
            []
        );
        $this->sessionRepository->create($session);
    
        return $session;
    }

    public function joinToSession($player, string $sessionId): void
    {
        $session = $this->sessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found.');
        }

        if (count($session->getConnections()) === $session->getCountOfConnections()) {
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

    public function setStart(string $sessionId, ?DateTime $time = null): Session
    {
        /** @var Session $session */
        $session = $this->sessionRepository->find($sessionId);

        if (!$session) {
            throw new NotFoundException('Session not found');
        }

        if (empty($session->getConnections())) {
            throw new DomainLogicalException('Cannot start empty session');
        }

        $startAt = $time
            ? $this->validateFutureTime($time)
            : (new DateTime())->modify('+5 seconds');

        $session->setStartAt($startAt);
        $this->sessionRepository->save($session);

        return $session;
    }

    private function validateFutureTime(DateTime $time): DateTime
    {
        if (new DateTime() >= $time) {
            throw new InvalidDataException(
                sprintf('Start time must be in future (%s)', $time->format('d.m.Y H:i:s'))
            );
        }
        return $time;
    }

    public function delete(string $sessionId): void
    {
        $this->sessionRepository->delete($sessionId);
    }

    public function findByConnection($conn): ?Session
    {
        $sessionId = $this->sessionRepository->findByConnection($conn);
        return is_null($sessionId) 
            ? null 
            : $this->sessionRepository->find($sessionId)
        ;
    }

    public function removeConnection(string $sessionId, ConnectionInterface $conn): void
    {
        $this->sessionRepository->removeConnection($sessionId, $conn);
    }
}
