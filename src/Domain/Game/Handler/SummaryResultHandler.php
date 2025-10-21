<?php

namespace App\Domain\Game\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Service\Scoring\SummaryService;
use App\Domain\Game\Event\SummaryResult;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;

class SummaryResultHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private GameRepositoryInterface $gameRepository,
        private SummaryService $summaryService,
        private ConnectionStorage $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return SummaryResult::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var SummaryResult $event */
        $sessionId = $event->getSessionId();
        $session = $this->sessionRepository->find($sessionId);
        $game = $this->gameRepository->find($session->getProcessId());
        $summary = $this->summaryService->summarize($game);

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
