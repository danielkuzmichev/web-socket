<?php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use React\Socket\SocketServer;

class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $loop;
    protected $games = []; // [gameId => ['players' => [], 'timers' => []]]

    public function __construct($loop) {
        $this->clients = new \SplObjectStorage();
        $this->loop = $loop;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->gameId = null;
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!isset($data['type'])) return;

        switch ($data['type']) {
            case 'create_game':
                $gameId = uniqid('game_');
                $from->gameId = $gameId;
                $this->games[$gameId] = [
                    'players' => [$from],
                    'timers' => [],
                ];
                $from->send(json_encode(['type' => 'game_created', 'game_id' => $gameId]));
                break;

            case 'join_game':
                $gameId = $data['game_id'] ?? null;
                if (!$gameId || !isset($this->games[$gameId])) {
                    $from->send(json_encode(['type' => 'error', 'message' => 'Game not found']));
                    return;
                }

                if (count($this->games[$gameId]['players']) >= 2) {
                    $from->send(json_encode(['type' => 'error', 'message' => 'Game full']));
                    return;
                }

                $from->gameId = $gameId;
                $this->games[$gameId]['players'][] = $from;

                // Notify both players
                foreach ($this->games[$gameId]['players'] as $player) {
                    $player->send(json_encode(['type' => 'game_ready']));
                }

                $this->startCountdown($gameId);
                break;
        }
    }

    public function startCountdown(string $gameId) {
        $players = $this->games[$gameId]['players'];
        $countdown = 3;

        $this->games[$gameId]['timers']['countdown'] = $this->loop->addPeriodicTimer(1, function () use (&$countdown, $players, $gameId) {
            foreach ($players as $player) {
                $player->send(json_encode(['type' => 'countdown', 'time' => $countdown]));
            }
            $countdown--;

            if ($countdown < 0) {
                $this->loop->cancelTimer($this->games[$gameId]['timers']['countdown']);
                $this->startGame($gameId);
            }
        });
    }

    public function startGame(string $gameId) {
        $players = $this->games[$gameId]['players'];
        $timeLeft = 15;

        $this->games[$gameId]['timers']['game'] = $this->loop->addPeriodicTimer(1, function () use (&$timeLeft, $players, $gameId) {
            foreach ($players as $player) {
                $player->send(json_encode(['type' => 'game_timer', 'time' => $timeLeft]));
            }
            $timeLeft--;

            if ($timeLeft < 0) {
                $this->loop->cancelTimer($this->games[$gameId]['timers']['game']);
                foreach ($players as $player) {
                    $player->send(json_encode(['type' => 'game_end', 'message' => 'Time is up!']));
                }
            }
        });
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";

        if ($conn->gameId && isset($this->games[$conn->gameId])) {
            $game = &$this->games[$conn->gameId];
            $game['players'] = array_filter($game['players'], fn($p) => $p !== $conn);
            if (empty($game['players'])) {
                foreach ($game['timers'] as $timer) {
                    $this->loop->cancelTimer($timer);
                }
                unset($this->games[$conn->gameId]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Boot server
$loop = Factory::create();
$webSock = new SocketServer('0.0.0.0:8080', [], $loop);
$webServer = new IoServer(
    new HttpServer(
        new WsServer(
            new GameServer($loop)
        )
    ),
    $webSock,
    $loop
);

$loop->run();
