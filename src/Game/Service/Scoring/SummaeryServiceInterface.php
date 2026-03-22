<?php

namespace App\Game\Service\Scoring;

interface SummaeryServiceInterface
{
    public function summarize(array $session): array;
}
