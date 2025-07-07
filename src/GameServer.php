<?php

namespace App;

use App\Core\Dispatcher\MessageDispatcherInterface;
use Ratchet\ConnectionInterface;
use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Util\Connection\ConnectionStorage;
use App\Util\Exception\ReturnableException;
use Ratchet\WebSocket\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{
    private MessageDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;
    private SessionRepositoryInterface $sessionRepository;

    public function __construct(
        MessageDispatcherInterface $dispatcher,
        ConnectionStorage $connectionStorage,
        SessionRepositoryInterface $sessionRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->connectionStorage = $connectionStorage;
        $this->sessionRepository = $sessionRepository;
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
        $sessionId = $this->sessionRepository->findByConnection($conn)['id'];

        if ($sessionId !== null) {
            $this->sessionRepository->removeConnection($sessionId, $conn);

            // Уведомляем других игроков, что кто-то вышел
            $sessionConns = $this->connectionStorage->getConnections($sessionId);
            //$sessionConns = $this->sessionRepository->find($sessionId);
            if (!empty($sessionConns)) {
                foreach ($sessionConns as $playerConn) {
                    $playerConn->send(json_encode([
                        'type' => 'player_left',
                        'payload' => [
                            'message' => 'Other player left the session.',
                            'sessionId' => $sessionId,
                            'departedPlayer' => $conn->id,
                        ]
                    ]));
                }
            }
            // $this->dispatcher->dispatchFromArray([
            //     'type' => 'player_left',
            //     'payload' => [
            //         'message' => 'Other player left the session.',
            //         'sessionId' => $sessionId,
            //         'departedPlayer' => $conn->id,
            //     ]
            // ]);
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
