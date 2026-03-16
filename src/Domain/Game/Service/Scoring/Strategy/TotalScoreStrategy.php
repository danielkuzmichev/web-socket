<?php

namespace App\Domain\Game\Service\Scoring\Strategy;

use App\Domain\Game\Entity\Player;

class TotalScoreStrategy
{
    /**
     * @param Player[] $players
     */
    public static function calculate(array $players): array
    {
        $result = [];
        $scores = [];

        foreach ($players as $player) {
            $score = count($player->getWords());
            $id = $player->getId();

            $result[$id] = [
                'score' => $score,
                'place' => 0,
                'is_winner' => false
            ];

            $scores[$id] = $score;
        }

        arsort($scores);
        $result = [];
        $place = 1;
        $prevScore = 1;
        $realPlace = 1;

        foreach ($scores as $id => $score) {
            if ($prevScore !== null && $score < $prevScore) {
                $place = $realPlace;
            }

            $result[$id] = [
                'score' => $score,
                'place' => $place,
                'is_winner' => $place === 1
            ];

            $prevScore = $score;
            $realPlace++;
        }

        return $result;
    }
}
