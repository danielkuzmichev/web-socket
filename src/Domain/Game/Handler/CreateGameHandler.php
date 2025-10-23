<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\CreateGame;
use App\Domain\Game\Repository\WordRepositoryInterface;
use App\Domain\Game\Service\GameServiceInterface;
use App\Domain\Session\Event\CreateSession;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Util\Exception\DuplicateException;
use Ratchet\ConnectionInterface;

class CreateGameHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private GameServiceInterface $gameService,
        private WordRepositoryInterface $wordRepository,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return CreateGame::class;
    }

    protected function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        if ($this->sessionService->findByConnection($conn)) {
            throw new DuplicateException('You already created or joined a session.');
        }
        /** @var CreateGame $event */
        $summaryType = $event->getSummaryType();
        $lang = $event->getLang();
        $gameId = uniqid(more_entropy: true);
        $this->gameService->createGame($gameId, $summaryType, $lang);
        $this->dispatcher->dispatch(new CreateSession($gameId, $event->getCountOfConnections()), $conn);
    }
}