<?php

namespace App\Domain\Game\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Session\Event\SessionStarted;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Connection\ConnectionStorageInterface;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class GameStartedHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private GameRepositoryInterface $gameRepository,
        private ConnectionStorageInterface $connectionStorage,
    ) {
    }

    public function getEventClass(): string
    {
        return SessionStarted::class;
    }

    protected function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var SessionStarted $event */
        $sessionId = $event->getSessionId();
        $session = $this->sessionRepository->find($sessionId);
        $game = $this->gameRepository->find($session->getProcessId());

        if ($game === null) {
            throw new NotFoundException('Game is not found');
        }

        $this->connectionStorage->broadcastToSession($sessionId, [
            'type' => 'match_started',
            'payload' => [
                'target_word' => $game->getWord(),
            ]
        ]);
    }
}