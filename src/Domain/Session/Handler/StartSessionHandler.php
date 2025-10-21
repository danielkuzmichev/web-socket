<?php

namespace App\Domain\Session\Handler;

use App\Domain\Session\Service\SessionServiceInterface;
use App\Domain\Session\Service\TimerService;
use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\SummaryResult;
use App\Domain\Session\Event\SessionStarted;
use App\Domain\Session\Event\StartSession;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;

class StartSessionHandler extends AbstractEventHandler
{
    private const DELETE_DELAY = 2;

    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WebSocketDispatcherInterface $dispatcher,
        private ConnectionStorage $connectionStorage,
        private SessionServiceInterface $sessionService,
        private TimerService $timerService,
        private int $gameStartDelay = 15
    ) {
    }

    public function getEventClass(): string
    {
        return StartSession::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var StartSession $event */
        $session = $this->sessionService->setStart($event->getSessionId());
        $startAt = $session->getStartAt();
        $delay = max(0, $startAt->getTimestamp() - time());

        // Отправляем обратный отсчёт всем игрокам
        $this->connectionStorage->broadcastToSession($event->getSessionId(), [
            'type' => 'countdown',
            'payload' => [
                'startAt' => $startAt,
                'remainingSeconds' => round($delay)
            ]
        ]);

        $this->timerService->add(
            $session->getId(),
            $delay,
            fn () => $this->startMatch($session->getId())
        );
    }

    private function startMatch(string $sessionId): void
    {
        $matchDuration = $this->gameStartDelay; // Длительность матча
        // Уведомляем всех, что матч начался
        $this->connectionStorage->broadcastToSession($sessionId, [
            'type' => 'match_started',
            'payload' => [
                'duration' => $matchDuration,
            ]
        ]);

        $this->dispatcher->dispatch(new SessionStarted($sessionId));

        $this->timerService->add(
            $sessionId,
            $matchDuration,
            fn () => $this->endMatch($sessionId)
        );
    }

    private function endMatch(string $sessionId): void
    {
        // Уведомляем всех, что матч завершён
        $this->connectionStorage->broadcastToSession($sessionId, [
            'type' => 'match_ended',
            'payload' => ['message' => 'Match ended!']
        ]);

        // Отправляем запрос на подсчёт результатов
        $this->dispatcher->dispatch(new SummaryResult($sessionId));

        // Удаляем сессию после небольшой задержки (чтобы клиенты успели получить результаты)
        Loop::get()->addTimer(self::DELETE_DELAY, function () use ($sessionId) {
            $this->sessionService->delete($sessionId);
            $this->connectionStorage->broadcastToSession($sessionId, [
                'type' => 'session_is_deleted',
                'payload' => ['message' => 'Session is deleted']
            ]);
        });
    }
}
