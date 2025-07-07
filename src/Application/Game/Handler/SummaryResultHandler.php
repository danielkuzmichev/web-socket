<?php

namespace App\Application\Game\Handler;

use App\Application\Game\Service\Scoring\SummaryService;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\Session\SessionRepositoryInterface as GameSessionGameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class SummaryResultHandler implements MessageHandlerInterface
{
    public function __construct(
        private GameSessionGameSessionRepositoryInterface $sessionRepository,
        private SummaryService $summaryService,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function getType(): string
    {
        return 'summarize_results';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        if (!isset($payload['sessionId']) || $payload['sessionId'] == null) {
            throw new InvalidDataException('Missing session ID.');
        }

        $sessionId = $payload['sessionId'];
        $session = $this->sessionRepository->find($sessionId);

        $summary = $this->summaryService->summarize($session);

        $connections = $this->connectionStorage->getConnections($sessionId);
        foreach ($connections as $playerConn) {
            $playerConn->send(json_encode([
                'type' => 'summarize_results',
                'payload' => [
                    'message' => 'Results are summarized',
                    'results' => isset($summary[$playerConn->resourceId]) && $summary[$playerConn->resourceId]['is_winner']
                ]
            ]));
        }
    }
}
