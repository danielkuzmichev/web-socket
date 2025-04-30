<?php

namespace App\Infrastructure\Repository\Word;

interface WordRepositoryInterface
{
    public function find(string $word): string;

    public function exists(string $word): bool;
}
