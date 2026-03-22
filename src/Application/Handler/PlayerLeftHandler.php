<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\GameEmpty;
use App\Application\Event\PlayerLeft;
use App\Game\Repository\GameRepositoryInterface;
use App\Core\Exception\NotFoundException;

class PlayerLeftHandler extends AbstractEventHandler
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return PlayerLeft::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var PlayerLeft $event */
        $sessionId = $event->getSessionId();
        $gameId = $event->getGameId();
        $playerToken = $event->getPlayerToken();
        $game = $this->gameRepository->find($gameId);

        if ($game === null) {
            throw new NotFoundException('Game is not found');
        }

        $game->removePlayerById($playerToken);
        $this->gameRepository->save($game);

        if (count($game->getPlayers()) <= 1) {
            $this->dispatcher->dispatch(new GameEmpty($sessionId, $gameId));
        }
    }
}
