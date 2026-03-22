<?php

namespace App\Game\Repository;

use App\Game\Entity\Game;

interface GameRepositoryInterface
{
    public function save(Game $game): void;

    public function find(string $id): ?Game;
}
