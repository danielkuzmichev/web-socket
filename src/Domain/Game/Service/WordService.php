<?php

namespace App\Domain\Game\Service;

use App\Domain\Game\Service\Scoring\SummaryService;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Domain\Game\Repository\WordRepositoryInterface;

class WordService implements WordServiceInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
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
        $wordExists = $this->check($word);
        $scoreWord = 0;
        $message = 'not_found_word';

        if ($wordExists) {
            $player = &$session['players'][$playerId];

            if (!in_array($word, $player['words'], true)) {
                $scoreWord = mb_strlen($word);
                $player['words'][] = $word;
                $message = 'found_word';
                if (isset($player['score'])) {
                    $player['score'] = $player['score'] + $scoreWord;
                } else {
                    $player['score'] = $scoreWord;
                }
            } else {
                $message = 'repeated_word';
            }

            $session['players'][$playerId] = $player;

            $this->sessionRepository->save($session);
        }

        return  [
            'score' => $scoreWord,
            'message' => $message,
        ];
    }

    public function checkLetters(string $checkedWord, string $targetWord): bool
    {
        $result = true;
        $checkedLetters = mb_str_split(mb_strtolower($checkedWord));
        $targetLetters = mb_str_split(mb_strtolower($targetWord));

        // Считаем количество каждой буквы в обоих словах
        $checkedCounts = array_count_values($checkedLetters);
        $targetCounts = array_count_values($targetLetters);

        // Проверяем каждую букву
        foreach ($checkedCounts as $letter => $count) {
            // Если буквы нет в целевом слове или её недостаточно
            if (!isset($targetCounts[$letter]) || $targetCounts[$letter] < $count) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}
