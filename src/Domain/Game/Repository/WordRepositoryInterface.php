<?php

namespace App\Domain\Game\Repository;

interface WordRepositoryInterface
{
    public function exists(string $word): bool;

    public function getRandomSessionWord(): string;
}
