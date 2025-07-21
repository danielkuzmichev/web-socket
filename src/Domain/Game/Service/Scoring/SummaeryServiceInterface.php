<?php

namespace App\Domain\Game\Service\Scoring;

interface SummaeryServiceInterface
{
    public function summarize(array $session): array;
}
