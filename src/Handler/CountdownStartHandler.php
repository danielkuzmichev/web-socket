<?php

namespace App\Handler;

use App\Repository\GameSessionRepositoryInterface;
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
        $seconds = $payload['delay'] ?? 3;
        $sessionId = $payload['sessionId'] ?? null;

        if (!$sessionId) {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Missing session ID for countdown.']
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

        $conn->send(json_encode([
            'type' => 'countdown',
            'payload' => ['message' => "Match starts in {$seconds} seconds..."]
        ]));


        $this->startTimer($seconds, $sessionId, $conn);
    }

    private function startTimer(int $countdownSeconds, string $sessionId, ConnectionInterface $conn): void
    {
        \React\EventLoop\Loop::get()->addTimer($countdownSeconds, function () use ($sessionId, $conn) {

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
