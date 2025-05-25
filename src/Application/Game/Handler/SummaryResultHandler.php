<?php

namespace App\Application\Game\Handler;

use App\Application\Game\Service\Scoring\SummaryService;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface as GameSessionGameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use Ratchet\ConnectionInterface;

class SummaryResultHandler implements MessageHandlerInterface
{
    public function __construct(
        private GameSessionGameSessionRepositoryInterface $sessionRepository,
        private WordRepositoryInterface $wordRepository,
        private SummaryService $summaryService
    ) {}

    public function getType(): string
    {
        return 'summarize_results';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        if (!isset($payload['sessionId']) || $payload['sessionId'] == null) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Missing session ID.']
            ]));
            return;
        }

        $sessionId = $payload['sessionId'];
        $session = $this->sessionRepository->find($sessionId);

        $summary = $this->summaryService->summarize($session);
        var_dump($conn->resourceId, $summary[$conn->resourceId]['is_winner']);
        $conn->send(json_encode([
            'type' => 'summarize_results',
            'payload' => [
                'message' => 'Results are summarized',
                'winner' => isset($summary[$conn->resourceId]) && $summary[$conn->resourceId]['is_winner']
            ]
        ]));
    }
}
