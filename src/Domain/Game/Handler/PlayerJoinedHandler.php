<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Entity\Player;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Session\Event\PlayerJoined;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class PlayerJoinedHandler extends AbstractEventHandler
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return PlayerJoined::class;
    }

    protected function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var PlayerJoined $event */

        $gameId = $event->getGameId();
        $playerId = $event->getConnectionId();
        $game = $this->gameRepository->find($gameId);

        if ($game === null) {
            throw new NotFoundException('Game is not found');
        }

        $player = new Player($playerId);
        $game->setPlayerByKey($playerId, $player);

        $this->gameRepository->save($game);
    }
}
