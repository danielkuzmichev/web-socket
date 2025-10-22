<?php

namespace App;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Domain\Game\Event\PlayerLeft;
use Ratchet\ConnectionInterface;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Infrastructure\Connection\ConnectionStorage;
use App\Util\Exception\ReturnableException;
use Psr\Log\LoggerInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{

    public function __construct(
        private WebSocketDispatcherInterface $dispatcher,
        private ConnectionStorage $connectionStorage,
        private SessionServiceInterface $sessionService,
        private LoggerInterface $logger,
    ) {
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->logger->info('Новое подключение', ['id' => $conn->resourceId]);
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
        $this->logger->info('Отключение', ['id' => $conn->resourceId]);
        // 1. Удаляем соединение из ConnectionStorage
        $this->connectionStorage->remove($conn);

        // 2. Удаляем соединение из GameSessionRepository

        $session = $this->sessionService->findByConnection($conn);
        $sessionId = $session?->getId();
        if ($sessionId !== null) {
            $this->sessionService->removeConnection($sessionId, $conn);

            $sessionConns = $this->connectionStorage->getConnections($sessionId);

            $event = new PlayerLeft($sessionId, $session->getProcessId(), $conn->resourceId);

            // Уведомляем других игроков, что кто-то вышел
            $this->dispatcher->dispatch($event);

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
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error($e->getMessage(),
            [
                'file' => "{$e->getFile()}:{$e->getLine()}", 
                'trace' => $e->getTrace(),
            ]
        );
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
