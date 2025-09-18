<?php

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Game;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Game\Repository\WordRepositoryInterface;

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