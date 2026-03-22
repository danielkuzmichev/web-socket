<?php

namespace App\Game\Repository;

interface WordRepositoryInterface
{
    public function exists(string $word): bool;

    public function getRandomSessionWord(): string;
}
