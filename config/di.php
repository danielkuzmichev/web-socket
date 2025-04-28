<?php

use App\Application\Handler\Session\CountdownStartHandler;
use App\Application\Handler\Session\CreateSessionHandler;
use App\Application\Handler\Session\JoinSessionHandler;
use App\Core\Dispatcher\MessageDispatcher;
use App\Core\Handler\MessageHandlerInterface;
use App\GameServer;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\GameSession\RedisGameSessionRepository;
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
    new JoinSessionHandler($gameSessionRepository, $connectionStorage, $dispatcher),
    new CountdownStartHandler($gameSessionRepository),
];

// Сообщаем диспетчеру о хендлерах
foreach ($handlers as $handler) {
    $dispatcher->registerHandler($handler);
}

$gameServer = new GameServer($dispatcher, $connectionStorage, $gameSessionRepository);

// Возвращаем всё нужное
return [
    'gameServer' => $gameServer,
    'handlers' => $handlers,
    'dispatcher' => $dispatcher,
];
