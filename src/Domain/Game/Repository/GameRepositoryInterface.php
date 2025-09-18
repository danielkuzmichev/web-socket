<?php

namespace App\Domain\Game\Repository;

use App\Domain\Game\Entity\Game;

interface GameRepositoryInterface
{
    public function save(Game $game): void;
}
