<?php

namespace App;

use App\Core\Dispatcher\MessageDispatcherInterface;
use Ratchet\ConnectionInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\ReturnableException;
use Ratchet\WebSocket\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{
    private MessageDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;
    private GameSessionRepositoryInterface $gameSessionRepository;

    public function __construct(
        MessageDispatcherInterface $dispatcher,
        ConnectionStorage $connectionStorage,
        GameSessionRepositoryInterface $gameSessionRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->connectionStorage = $connectionStorage;
        $this->gameSessionRepository = $gameSessionRepository;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if ($data) {
            $this->dispatcher->dispatchFromArray($data, $from);
        } else {
            $from->send(json_encode([
                'type' => 'error',
                'payload' => ['message' => 'Invalid message format']
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        // 1. Удаляем соединение из ConnectionStorage
        $this->connectionStorage->remove($conn);

        // 2. Удаляем соединение из GameSessionRepository
        $sessionId = $this->gameSessionRepository->findByConnection($conn)['id'];

        if ($sessionId !== null) {
            $this->gameSessionRepository->removeConnection($sessionId, $conn);

            // (опционально) Уведомляем других игроков, что кто-то вышел
            // $session = $this->gameSessionRepository->find($sessionId);
            // if ($session) {
            //     foreach ($session['players'] as $playerConn) {
            //         $playerConn->send(json_encode([
            //             'type' => 'player_left',
            //             'payload' => [
            //                 'message' => 'Other player left the session.',
            //                 'sessionId' => $sessionId
            //             ]
            //         ]));
            //     }
            // }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->send(json_encode([
            'type' => 'error',
            'payload' => ['message' => $e->getMessage(), 'code' => $e->getCode()]
        ]));
        if (!$e instanceof ReturnableException) {
            $conn->close();
        }
    }
}
