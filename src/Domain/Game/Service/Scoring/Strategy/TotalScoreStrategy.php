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
        
        foreach($players as $player) {
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
        
        $place = 1;
        $previousScore = null;
        $actualPlace = 1;
        
        foreach($scores as $id => $score) {
            if ($previousScore !== null && $score !== $previousScore) {
                $actualPlace = $place;
            }
            
            $result[$id]['place'] = $actualPlace;
            $result[$id]['is_winner'] = ($actualPlace === 1);
            
            $previousScore = $score;
            $place++;
        }
        
        return $result;
    }
}
