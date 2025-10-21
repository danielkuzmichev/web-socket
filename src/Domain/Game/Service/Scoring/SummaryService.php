<?php

namespace App\Domain\Game\Service\Scoring;

use App\Domain\Game\Entity\Game;
use App\Domain\Game\Enum\GameType;
use App\Domain\Game\Service\Scoring\Strategy\UniqueWordsByLengthStrategy;

class SummaryService
{
    public function summarize(Game $game): array
    {
        $players = $game->getPlayers();
        $summaryType = $game->getSummaryType();

        $result = match($summaryType) {
            GameType::UNIQUE_WORDS_BY_LENGTH => UniqueWordsByLengthStrategy::calculate($players),
            default => UniqueWordsByLengthStrategy::calculate($players)
        };

        return $result;
    }
}
