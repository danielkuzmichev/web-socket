<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\GameSummaryReady;
use App\Application\Event\SummaryResult;
use App\Game\Repository\GameRepositoryInterface;
use App\Game\Service\Scoring\SummaryService;
use App\Session\Repository\SessionRepositoryInterface;

class SummaryResultHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private GameRepositoryInterface $gameRepository,
        private SummaryService $summaryService,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return SummaryResult::class;
    }

    public function process(EventInterface $event): void
    {
        /** @var SummaryResult $event */
        $sessionId = $event->getSessionId();
        $session = $this->sessionRepository->find($sessionId);
        $game = $this->gameRepository->find($session->getProcessId());
        $summary = $this->summaryService->summarize($game);

        $this->dispatcher->dispatch(new GameSummaryReady($sessionId, $summary));
    }
}
