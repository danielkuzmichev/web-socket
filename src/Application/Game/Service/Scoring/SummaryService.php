<?php

namespace App\Application\Game\Service\Scoring;

use App\Application\Game\Service\Scoring\Strategy\UniqueWordsByLengthStrategy;

class SummaryService
{
    public function summarize(array $session): array
    {
        $players = $session['players'];
        $summaryType = $session['summary_type'];

        $result = match($summaryType) {
            'unique_words_by_length' => UniqueWordsByLengthStrategy::calculate($players),
            default => UniqueWordsByLengthStrategy::calculate($players)
        };

        return $result;
    }
}
