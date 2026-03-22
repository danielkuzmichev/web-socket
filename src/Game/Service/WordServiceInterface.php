<?php

namespace App\Game\Service;

use App\Game\Entity\Game;

interface WordServiceInterface
{
    public function check(string $word): bool;

    public function score(string $word, string $playerToken, Game $game): mixed;

    public function checkLetters(string $checkedWord, string $targetWord): bool;
}
