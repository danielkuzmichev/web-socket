<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\CreateGame;
use App\Domain\Game\Event\CreatedGame;
use App\Domain\Game\Service\GameServiceInterface;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Util\Exception\DuplicateException;

class CreateGameHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private GameServiceInterface $gameService,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return CreateGame::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var CreateGame $event */
        if ($this->sessionService->existByPlayerToken($event->getPlayerToken())) {
            throw new DuplicateException('You already created or joined a session.');
        }
        /** @var CreateGame $event */
        $summaryType = $event->getSummaryType();
        $gameId = uniqid(more_entropy: true);
        $this->gameService->createGame($gameId, $summaryType);
        $this->dispatcher->dispatch(new CreatedGame($gameId, $event->getCountOfConnections(), $event->getPlayerToken()));
    }
}
