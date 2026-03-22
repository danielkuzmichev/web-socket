<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\MatchStarted;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Session\Event\SessionStarted;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\NotFoundException;

class GameStartedHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private GameRepositoryInterface $gameRepository,
        private WebSocketDispatcherInterface $dispatcher,
    ) {
    }

    public function getEventClass(): string
    {
        return SessionStarted::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var SessionStarted $event */
        $sessionId = $event->getSessionId();
        $session = $this->sessionRepository->find($sessionId);
        if ($session === null) {
            return;
        }

        $game = $this->gameRepository->find($session->getProcessId());

        if ($game === null) {
            throw new NotFoundException('Game is not found');
        }

        $this->dispatcher->dispatch(new MatchStarted($sessionId, $game->getWord()));
    }
}
