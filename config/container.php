<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/event_map_builder.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->setParameter('kernel.project_dir', dirname(__DIR__));
$containerBuilder->setParameter('kernel.logs_dir', dirname(__DIR__) . '/var/log');


$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('services.yml');

// event_map
$eventMap = build_event_map(__DIR__ . '/../src/Domain/*/Event');
$containerBuilder->setParameter('event_map', $eventMap);

// Logger
$connectionsHandler = new StreamHandler(dirname(__DIR__) . '/var/log/websocket_info.log', Level::Info);
$errorsHandler = new StreamHandler(dirname(__DIR__) . '/var/log/websocket_error.log', Level::Error);
$infoHandler = new \Monolog\Handler\FilterHandler($connectionsHandler, Level::Info, Level::Notice);
$websocketLogger = new Logger('websocket');
$websocketLogger->pushHandler($infoHandler);
$websocketLogger->pushHandler($errorsHandler);
$containerBuilder->set('websocket.logger', $websocketLogger);
set_exception_handler(function (Throwable $e) use ($websocketLogger) {
    $websocketLogger->error('Uncaught exception: ' . $e::class, [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
});

$containerBuilder->setAlias(Psr\Log\LoggerInterface::class, 'websocket.logger');

$containerBuilder->compile();

return $containerBuilder;
