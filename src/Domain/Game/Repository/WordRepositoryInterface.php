<?php

namespace App\Domain\Game\Repository;

interface WordRepositoryInterface
{
    public function exists(string $word, string $lang): bool;

    public function getRandomSessionWord(string $lang): string;
}
