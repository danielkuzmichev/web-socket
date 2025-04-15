<?php

namespace App\Handler;

use Ratchet\ConnectionInterface;
use App\Repository\InMemoryGameSessionRepository;

class CountdownStartHandler implements MessageHandlerInterface {

    public function getType(): string {
        return 'countdown_start';
    }

    public function handle(array $payload, ConnectionInterface $conn): void {
        $seconds = $payload['delay'] ?? 3;
        $sessionId = $payload['sessionId'] ?? null;

        if (!$sessionId) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Missing session ID for countdown.']
            ]));
            return;
        }

        $repository = InMemoryGameSessionRepository::getInstance();
        $session = $repository->find($sessionId);

        if (!$session || count($session['players']) < 2) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Invalid or incomplete session.']
            ]));
            return;
        }

        // Уведомляем игроков о начале обратного отсчёта
        foreach ($session['players'] as $playerConn) {
            if($conn == $playerConn) {
                $playerConn->send(json_encode([
                    'type' => 'countdown',
                    'payload' => ['message' => "Match starts in {$seconds} seconds..."]
                ]));
            }
        }

        // Запускаем таймер
        $this->startTimer($seconds, $sessionId, $conn);
    }

    private function startTimer(int $countdownSeconds, string $sessionId, ConnectionInterface $conn): void {
        // Отложенный запуск, чтобы не блокировать основной поток
        \React\EventLoop\Loop::get()->addTimer($countdownSeconds, function () use ($sessionId, $conn) {
            $repository = InMemoryGameSessionRepository::getInstance();
            $session = $repository->find($sessionId);

            if (!$session) return;

            foreach ($session['players'] as $playerConn) {
                if($conn == $playerConn) {
                    $playerConn->send(json_encode([
                        'type' => 'match_started',
                        'payload' => ['duration' => 15]
                    ]));
                }
            }

            // Запускаем таймер окончания матча через 15 секунд
            \React\EventLoop\Loop::get()->addTimer(15, function () use ($sessionId, $conn) {
                $repository = InMemoryGameSessionRepository::getInstance();
                $session = $repository->find($sessionId);

                if (!$session) return;

                foreach ($session['players'] as $playerConn) {
                    if($conn == $playerConn) {
                        $playerConn->send(json_encode([
                            'type' => 'match_ended',
                            'payload' => ['message' => 'Match ended!']
                        ]));
                    }
                }
            });
        });
    }
}
