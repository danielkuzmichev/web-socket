<?php

namespace App\Domain\Game\Handler;

use App\Domain\Game\Service\Scoring\SummaryService;
use App\Core\Handler\MessageHandlerInterface;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use App\Util\Exception\InvalidDataException;
use Ratchet\ConnectionInterface;

class SummaryResultHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
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
