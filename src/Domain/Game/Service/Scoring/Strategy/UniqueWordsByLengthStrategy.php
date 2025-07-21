<?php

namespace App\Domain\Game\Service\Scoring\Strategy;

class UniqueWordsByLengthStrategy
{
    public static function calculate(array $players): array
    {
        $wordOwnership = [];
        foreach ($players as $player) {
            if (isset($player['words'])) {
                foreach ($player['words'] as $word) {
                    $word = mb_strtolower($word);
                    $wordOwnership[$word][] = $player['connection_id'];
                }
            }
        }

        $uniqueWords = array_filter($wordOwnership, fn ($owners) => count($owners) === 1);

        // Подсчет очков
        $scores = [];
        foreach ($players as $player) {
            $score = 0;
            $id = $player['connection_id'];

            if (isset($player['words'])) {
                foreach ($player['words'] as $word) {
                    $word = mb_strtolower($word);
                    if (isset($uniqueWords[$word]) && $uniqueWords[$word][0] === $id) {
                        $score += mb_strlen($word);
                    }
                }
            }

            $scores[$id] = $score;
        }

        // Определение мест и победителя
        arsort($scores);
        $result = [];
        $place = 1;
        $prevScore = null;
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
