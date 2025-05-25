<?php

namespace App\Infrastructure\Repository\Word;

interface WordRepositoryInterface
{
    public function exists(string $word): bool;

    public function getRandomSessionWord(): string;
}
