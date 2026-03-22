<?php

namespace App\Application\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Game\Entity\Player;
use App\Application\Event\PlayerJoined;
use App\Game\Repository\GameRepositoryInterface;
use App\Core\Exception\NotFoundException;

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

    protected function process(EventInterface $event): void
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
