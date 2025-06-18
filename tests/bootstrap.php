<?php

use Tests\TestContainerLocator;

require __DIR__ . '/../vendor/autoload.php';

// Основной контейнер (продакшн)
$container = require __DIR__ . '/../config/container.php';


TestContainerLocator::set($container);
