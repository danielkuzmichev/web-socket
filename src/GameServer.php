<?php

namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Dispatcher\MessageDispatcherInterface;
use App\Repository\GameSessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;

class GameServer implements MessageComponentInterface
{
    private MessageDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;
    private GameSessionRepositoryInterface $gameSessionRepository;

    public function __construct(MessageDispatcherInterface $dispatcher, ConnectionStorage $connectionStorage)
    {
        $this->dispatcher = $dispatcher;
        $this->connectionStorage = $connectionStorage;
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
        $sessionId = $this->gameSessionRepository->findByConnection($conn);

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
        $conn->close();
    }
}
