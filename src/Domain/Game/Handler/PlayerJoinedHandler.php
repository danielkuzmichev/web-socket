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
        private GameRepositoryInterface $gameRepository
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
        $playerToken = $event->getPlayerToken();
        $game = $this->gameRepository->find($gameId);

        if ($game === null) {
            throw new NotFoundException('Game is not found');
        }

        $player = new Player($playerToken);
        $game->setPlayerByKey($playerToken, $player);

        $this->gameRepository->save($game);
    }
}
