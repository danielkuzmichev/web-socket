<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\GameServer;
use App\Dispatcher\MessageDispatcherInterface;

// Получаем DI контейнер
$container = require __DIR__ . '/../config/di.php';

// Извлекаем необходимые зависимости из контейнера
$gameServer = $container[GameServer::class];

// Создаем и запускаем сервер
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $gameServer
        )
    ),
    8080
);

echo "Server started on port 8080...\n";
$server->run();
