<?php

namespace App\Application\Game\Service\Scoring;

interface SummaeryServiceInterface
{
    public function summarize(array $session): array;
}
