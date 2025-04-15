<?php

use App\Dispatcher\MessageDispatcher;
use App\Dispatcher\MessageDispatcherInterface;
use App\Handler\CountdownStartHandler;
use App\Handler\JoinSessionHandler;
use App\Handler\CreateSessionHandler;
use App\Repository\InMemoryGameSessionRepository;
use App\Repository\GameSessionRepositoryInterface;
use App\GameServer;

require_once __DIR__ . '/../vendor/autoload.php';

// Репозиторий
$gameSessionRepository = InMemoryGameSessionRepository::getInstance();

// Обработчики
$joinHandler = new JoinSessionHandler($gameSessionRepository, null);
$countdownHandler = new CountdownStartHandler($gameSessionRepository);
$createSessionHandler = new CreateSessionHandler($gameSessionRepository);

// Диспетчер
$dispatcher = new MessageDispatcher([
    $joinHandler,
    $countdownHandler,
    $createSessionHandler
]);

// Подключаем обратно ссылку на диспетчер в JoinSessionHandler
$reflection = new \ReflectionClass($joinHandler);
$property = $reflection->getProperty('dispatcher');
$property->setAccessible(true);
$property->setValue($joinHandler, $dispatcher);

// Инжектим MessageDispatcher в GameServer
$gameServer = new GameServer($dispatcher);

// Возврат экземпляров
return [
    MessageDispatcherInterface::class => $dispatcher,
    GameSessionRepositoryInterface::class => $gameSessionRepository,
    JoinSessionHandler::class => $joinHandler,
    CountdownStartHandler::class => $countdownHandler,
    CreateSessionHandler::class => $createSessionHandler,
    GameServer::class => $gameServer,
];
