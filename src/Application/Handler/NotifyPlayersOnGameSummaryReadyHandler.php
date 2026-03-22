<?php

namespace App\Application\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\GameSummaryReady;
use App\Connection\ConnectionStorageInterface;

class NotifyPlayersOnGameSummaryReadyHandler extends AbstractEventHandler
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return GameSummaryReady::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var GameSummaryReady $event */
        $sessionId = $event->getSessionId();
        $results = $event->getResults();

        $connections = $this->connectionStorage->getConnections($sessionId);

        foreach ($connections as $playerConn) {
            $playerToken = $this->connectionStorage->getTokenByConnection($playerConn);
            $playerResult = $playerToken !== null && isset($results[$playerToken])
                ? $results[$playerToken]
                : null;
            $isWinner = is_array($playerResult) && isset($playerResult['is_winner'])
                ? (bool) $playerResult['is_winner']
                : false;

            $playerConn->send(json_encode([
                'type' => 'summarize_results',
                'payload' => [
                    'message' => 'Results are summarized',
                    'results' => $isWinner,
                    'summary' => $playerResult,
                ]
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        }
    }
}
