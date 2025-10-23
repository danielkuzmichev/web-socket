<?php

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Game;

interface WordServiceInterface
{
    public function check(string $word, string $lang): bool;

    public function score(string $word, mixed $playerId, Game $session): mixed;

    public function checkLetters(string $checkedWord, string $targetWord): bool;
}
