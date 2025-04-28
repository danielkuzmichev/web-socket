<?php

namespace App\Application\Handler\Session;

use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use Ratchet\ConnectionInterface;

class CountdownStartHandler implements MessageHandlerInterface
{
    private GameSessionRepositoryInterface $gameSessionRepository;

    public function __construct(GameSessionRepositoryInterface $gameSessionRepository)
    {
        $this->gameSessionRepository = $gameSessionRepository;
    }

    public function getType(): string
    {
        return 'countdown_start';
    }

    /** @todo Проверить, что заканчивается одновременно */
    public function handle(array $payload, ConnectionInterface $conn): void
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

        $session = $this->gameSessionRepository->find($sessionId);

        if (!$session || count($session['players']) < 2) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Invalid or incomplete session.']
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

        $this->startTimer($delay, $conn);
    }

    private function startTimer(float $delaySeconds, ConnectionInterface $conn): void
    {
        \React\EventLoop\Loop::get()->addTimer($delaySeconds, function () use ($conn) {

            $conn->send(json_encode([
                'type' => 'match_started',
                'payload' => ['duration' => 15]
            ]));

            \React\EventLoop\Loop::get()->addTimer(15, function () use ($conn) {
                $conn->send(json_encode([
                    'type' => 'match_ended',
                    'payload' => ['message' => 'Match ended!']
                ]));
            });
        });
    }
}
