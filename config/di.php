<?php

use App\Dispatcher\MessageDispatcher;
use App\GameServer;
use App\Handler\CreateSessionHandler;
use App\Handler\JoinSessionHandler;
use App\Handler\CountdownStartHandler;
use App\Handler\MessageHandlerInterface;
use App\Repository\GameSessionRepositoryInterface;
use App\Repository\InMemoryGameSessionRepository;
use App\Repository\RedisGameSessionRepository;
use App\Util\Connection\ConnectionStorage;
use App\Util\Redis\RedisClient;

$container = [];

$redisClient = new RedisClient();
$redisClient->connect('redis', 6379);

$connectionStorage = new ConnectionStorage();

$container[ConnectionStorage::class] = $connectionStorage;

$container[GameSessionRepositoryInterface::class] = new RedisGameSessionRepository($redisClient);

$container[MessageDispatcher::class] = new MessageDispatcher();

/** @var GameSessionRepositoryInterface $gameSessionRepository */
$gameSessionRepository = $container[GameSessionRepositoryInterface::class];

/** @var MessageDispatcher $dispatcher */
$dispatcher = $container[MessageDispatcher::class];

// Регистрируем все обработчики
/** @var MessageHandlerInterface[] $handlers */
$handlers = [
    new CreateSessionHandler($gameSessionRepository, $connectionStorage),
    new JoinSessionHandler($gameSessionRepository, $dispatcher, $connectionStorage),
    new CountdownStartHandler($gameSessionRepository),
];

// Сообщаем диспетчеру о хендлерах
foreach ($handlers as $handler) {
    $dispatcher->registerHandler($handler);
}

$gameServer = new GameServer($dispatcher, $connectionStorage);

// Возвращаем всё нужное
return [
    'gameServer' => $gameServer,
    'handlers' => $handlers,
    'dispatcher' => $dispatcher,
];
