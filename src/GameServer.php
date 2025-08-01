<?php

namespace App;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Domain\Game\Event\PlayerLeft;
use Ratchet\ConnectionInterface;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use App\Util\Exception\ReturnableException;
use Ratchet\WebSocket\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{
    private WebSocketDispatcherInterface $dispatcher;
    private ConnectionStorage $connectionStorage;
    private SessionRepositoryInterface $sessionRepository;

    public function __construct(
        WebSocketDispatcherInterface $dispatcher,
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

            $event = new PlayerLeft($sessionId, $conn->resourceId);

            if (!empty($sessionConns)) {
                foreach ($sessionConns as $playerConn) {
                    $playerConn->send(json_encode([
                        'type' => 'player_left',
                        'payload' => [
                            'message' => 'Other player left the session.',
                            'sessionId' => $sessionId,
                            'departedPlayer' => $conn->resourceId,
                        ]
                    ]));
                }
            }
            $this->dispatcher->dispatch($event);
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
