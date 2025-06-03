<?php

namespace App\Application\Session\Handler;

use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;

class CountdownStartHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;
    private MessageDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;

    public function __construct(
        GameSessionRepositoryInterface $gameSessionRepository,
        MessageDispatcherInterface $dispatcher,
        ConnectionStorage $connectionStorage,
    ) {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->dispatcher = $dispatcher;
        $this->connectionStorage = $connectionStorage;
    }

    public function getType(): string
    {
        return 'countdown_start';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $session = $payload['session'] ?? null;

        if (!$session) {
            $conn?->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'There is no session']
            ]));
            return;
        }

        if (empty($session['players'])) {
            $conn?->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'There are no players in the session.']
            ]));
            return;
        }

        $sessionId = $session['id'];
        $startAt = microtime(true) + 5; // Запуск через 5 секунд
        $delay = max(0, $startAt - microtime(true));

        // Отправляем обратный отсчёт всем игрокам
        $this->broadcastToSession($sessionId, [
            'type' => 'countdown',
            'payload' => [
                'startAt' => $startAt,
                'remainingSeconds' => round($delay)
            ]
        ]);

        // Запускаем таймер для старта матча
        Loop::get()->addTimer($delay, function () use ($sessionId) {
            $this->startMatch($sessionId);
        });
    }

    private function startMatch(string $sessionId): void
    {
        $matchDuration = 15; // Длительность матча (сек)

        // Уведомляем всех, что матч начался
        $this->broadcastToSession($sessionId, [
            'type' => 'match_started',
            'payload' => ['duration' => $matchDuration]
        ]);

        // Запускаем таймер завершения матча
        Loop::get()->addTimer($matchDuration, function () use ($sessionId) {
            $this->endMatch($sessionId);
        });
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
            $this->gameSessionRepository->delete($sessionId);
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
