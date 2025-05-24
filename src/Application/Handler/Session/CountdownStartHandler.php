<?php

namespace App\Application\Handler\Session;

use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use Ratchet\ConnectionInterface;

class CountdownStartHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;
    private MessageDispatcherInterface $dispatcher;

    public function __construct(GameSessionRepositoryInterface $gameSessionRepository, MessageDispatcherInterface $dispatcher)
    {
        $this->gameSessionRepository = $gameSessionRepository;
        $this->dispatcher = $dispatcher;
    }

    public function getType(): string
    {
        return 'countdown_start';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $startAt = $payload['startAt'] ?? null;
        $sessionId = $payload['sessionId'] ?? null;

        if (!$startAt || !$sessionId) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Missing start time or session ID.']
            ]));
            return;
        }

        $now = microtime(true);
        $delay = max(0, $startAt - $now);

        $conn->send(json_encode([
            'type' => 'countdown',
            'payload' => [
                'startAt' => $startAt,
                'remainingSeconds' => round($delay)
            ]
        ]));

        $this->startTimer($delay, $conn, $sessionId);
    }

    private function startTimer(float $delaySeconds, ConnectionInterface $conn, string $sessionId): void
    {
        \React\EventLoop\Loop::get()->addTimer($delaySeconds, function () use ($conn, $sessionId) {

            $conn->send(json_encode([
                'type' => 'match_started',
                'payload' => ['duration' => 15]
            ]));

            \React\EventLoop\Loop::get()->addTimer(15, function () use ($conn, $sessionId) {
                $conn->send(json_encode([
                    'type' => 'match_ended',
                    'payload' => [
                        'message' => 'Match ended!',
                    ]
                ]));

                $this->dispatcher->dispatchFromArray([
                    'type' => 'summarize_results',
                    'payload' => [
                        'sessionId' => $sessionId
                    ]
                ], $conn);
            });

            /** @todo убрать в ходе рефакторинга */
            \React\EventLoop\Loop::get()->addTimer(18, function () use ($conn, $sessionId) {
                $this->gameSessionRepository->delete($sessionId);
                $conn->send(json_encode([
                    'type' => 'session_is_deleted',
                    'payload' => [
                        'message' => 'Session is deleted',
                    ]
                ]));
            });
        });
    }
}
