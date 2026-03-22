<?php

namespace App\Game\Service;

interface GameServiceInterface
{
    public function createGame(string $id, string $summaryType): mixed;
}