<?php

namespace App\Application\Game\Service;

use App\Application\Game\Service\Scoring\SummaryService;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;

class WordService
{
    public function __construct(
        private GameSessionRepositoryInterface $sessionRepository,
        private WordRepositoryInterface $wordRepository,
        private SummaryService $summaryService
    ) {
    }

    public function check(string $word): bool
    {
        return $this->wordRepository->exists($word);
    }

    public function score(string $word, mixed $playerId, mixed $session): mixed
    {
        $score = [];
        $wordExists = $this->check($word);

        if ($wordExists) {
            $score = mb_strlen($word);

            $player = &$session['players'][$playerId];

            if (!in_array($word, $player['words'], true)) {
                $player['words'][] = $word;
                if (isset($player['score'])) {
                    $player['score'] = $player['score'] + $score;
                } else {
                    $player['score'] = $score;
                }
            }

            $session['players'][$playerId] = $player;

            $this->sessionRepository->save($session);

            $score = ['score' => $score];
        }

        return $score;
    }
}
