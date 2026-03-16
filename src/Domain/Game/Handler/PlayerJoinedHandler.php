<?php

namespace App\Domain\Game\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Entity\Player;
use App\Domain\Game\Event\PlayerJoined;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Util\Exception\NotFoundException;

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
