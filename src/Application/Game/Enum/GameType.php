<?php

namespace App\Application\Game\Enum;

enum GameType: string
{
    case UNIQUE_WORDS_BY_LENGTH = 'unique_words_by_length';
    case TOTAL_SCORE = 'total_score';
}
