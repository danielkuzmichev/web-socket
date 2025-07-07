<?php

namespace App\Application\Session\Handler;

use App\Application\Session\Service\SessionServiceInterface;
use App\Application\Session\Service\TimerService;
use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;

class StartSessionHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private MessageDispatcherInterface $dispatcher,
        private ConnectionStorage $connectionStorage,
        private SessionServiceInterface $sessionService,
        private TimerService $timerService
    ) {
    }

    public function getType(): string
    {
        return 'start_session';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $session = $this->sessionService->setStart($payload['sessionId']);
        $startAt = $session['startAt'] + 5;
        $delay = max(0, $startAt - microtime(true));

        // Отправляем обратный отсчёт всем игрокам
        $this->broadcastToSession($payload['sessionId'], [
            'type' => 'countdown',
            'payload' => [
                'startAt' => $startAt,
                'remainingSeconds' => round($delay)
            ]
        ]);

        $this->timerService->add(
            $session['id'],
            $delay,
            fn () => $this->startMatch($session['id'])
        );
    }

    private function startMatch(string $sessionId): void
    {
        $matchDuration = 15; // Длительность матча
        // Уведомляем всех, что матч начался
        $this->broadcastToSession($sessionId, [
            'type' => 'match_started',
            'payload' => ['duration' => $matchDuration]
        ]);

        $this->timerService->add(
            $sessionId,
            $matchDuration,
            fn () => $this->endMatch($sessionId)
        );
    }

    private function endMatch(string $sessionId): void
    {
        // Уведомляем всех, что матч завершён
        $this->broadcastToSession($sessionId, [
            'type' => 'match_ended',
            'payload' => ['message' => 'Match ended!']
        ]);

        // Отправляем запрос на подсчёт результатов
        $this->dispatcher->dispatchFromArray([
            'type' => 'summarize_results',
            'payload' => ['sessionId' => $sessionId]
        ]);

        // Удаляем сессию после небольшой задержки (чтобы клиенты успели получить результаты)
        Loop::get()->addTimer(2, function () use ($sessionId) {
            $this->sessionService->delete($sessionId);
            $this->broadcastToSession($sessionId, [
                'type' => 'session_is_deleted',
                'payload' => ['message' => 'Session is deleted']
            ]);
        });
    }

    private function broadcastToSession(string $sessionId, array $message): void
    {
        $connections = $this->connectionStorage->getConnections($sessionId);
        foreach ($connections as $conn) {
            $conn->send(json_encode($message));
        }
    }
}
