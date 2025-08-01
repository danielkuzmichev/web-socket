<?php

use App\Core\EventMap\EventMapBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('services.yml');
$eventMap = EventMapBuilder::build(__DIR__ . '/../src/Domain/*/Event');
$containerBuilder->setParameter('event_map', $eventMap);
$containerBuilder->compile();

return $containerBuilder;
