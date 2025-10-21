<?php

namespace App\Domain\Game\Service\Scoring\Strategy;

use App\Domain\Game\Entity\Player;

class UniqueWordsByLengthStrategy
{
    public static function calculate(array $players): array
    {
        $wordOwnership = [];
        /** @var Player $player */
        foreach ($players as $id => $player) {
            foreach ($player->getWords() as $word) {
                $word = mb_strtolower($word);
                $wordOwnership[$word][] = $id;
            }
        }

        $uniqueWords = array_filter($wordOwnership, fn ($owners) => count($owners) === 1);

        // Подсчет очков
        $scores = [];
        foreach ($players as $id => $player) {
            $score = 0;
            foreach ($player->getWords() as $word) {
                $word = mb_strtolower($word);
                if (isset($uniqueWords[$word]) && $uniqueWords[$word][0] === $id) {
                    $score += mb_strlen($word);
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
