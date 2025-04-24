<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\GameServer;

$container = require __DIR__ . '/../config/container.php';


$gameServer = $container->get(App\GameServer::class);

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
