<?php

namespace App\Domain\Game\Service\Scoring;

use App\Domain\Game\Enum\GameType;
use App\Domain\Game\Service\Scoring\Strategy\UniqueWordsByLengthStrategy;

class SummaryService
{
    public function summarize(array $session): array
    {
        $players = $session['players'];
        $summaryType = $session['summary_type'];

        $result = match($summaryType) {
            GameType::UNIQUE_WORDS_BY_LENGTH => UniqueWordsByLengthStrategy::calculate($players),
            default => UniqueWordsByLengthStrategy::calculate($players)
        };

        return $result;
    }
}
