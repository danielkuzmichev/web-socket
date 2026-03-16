<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\GameSummaryReady;
use App\Domain\Game\Event\SummaryResult;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Game\Service\Scoring\SummaryService;
use App\Domain\Session\Repository\SessionRepositoryInterface;

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
