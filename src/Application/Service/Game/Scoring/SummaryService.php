<?php

namespace App\Application\Service\Game\Scoring;

use App\Application\Service\Game\Scoring\Strategy\UniqueWordsByLengthStrategy;

class SummaryService
{
    public function summarize(array $session): array
    {
        $players = $session['players'];
        $summaryType = $session['summary_type'];

        $result = match($summaryType) {
            'unique_words_by_length' => UniqueWordsByLengthStrategy::calculate($players),
        };

        return $result;
    }
}
