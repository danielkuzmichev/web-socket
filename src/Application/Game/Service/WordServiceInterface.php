<?php

namespace App\Application\Game\Service;

interface WordServiceInterface
{
    public function check(string $word): bool;

    public function score(string $word, mixed $playerId, mixed $session): mixed;

    public function checkLetters(string $checkedWord, string $targetWord): bool;
}
