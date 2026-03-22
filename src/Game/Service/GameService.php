<?php

namespace App\Game\Service;

use App\Game\Entity\Game;
use App\Game\Repository\GameRepositoryInterface;
use App\Game\Repository\WordRepositoryInterface;

class GameService implements GameServiceInterface
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
        private WordRepositoryInterface $wordRepository,
    ) {
    }

    public function createGame(string $id, string $summaryType): Game
    {
        $sessionWord = $this->wordRepository->getRandomSessionWord();
        $game = new Game(
            $id,
            $sessionWord,
            $summaryType,
            []
        );
        $this->gameRepository->save($game);

        return $game;
    }
}