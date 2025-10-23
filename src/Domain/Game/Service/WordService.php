<?php

namespace App\Domain\Game\Service;

use App\Domain\Game\Entity\Game;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Game\Service\Scoring\SummaryService;
use App\Domain\Game\Repository\WordRepositoryInterface;
use App\Domain\Game\Entity\Player;

class WordService implements WordServiceInterface
{
    public function __construct(
        private GameRepositoryInterface $gameRepository,
        private WordRepositoryInterface $wordRepository,
        private SummaryService $summaryService
    ) {
    }

    public function check(string $word, string $lang): bool
    {
        return $this->wordRepository->exists($word, $lang);
    }

    public function score(string $word, mixed $playerId, Game $game): mixed
    {
        $wordExists = $this->check($word, $game->getLang());
        $scoreWord = 0;
        $message = 'not_found_word';

        if ($wordExists) {
            /** @var Player $player */
            $player = &$game->getPlayerByKey($playerId);
            if (!in_array($word, $player->getWords(), true)) {
                $scoreWord = mb_strlen($word);
                $player->addWord($word);
                $message = 'found_word';
                $player->addScore($scoreWord);
            } else {
                $message = 'repeated_word';
            }

            $game->setPlayerByKey($playerId, $player);

            $this->gameRepository->save($game);
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
