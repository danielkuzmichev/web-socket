<?php

require __DIR__ . '/../vendor/autoload.php';

// Основной контейнер (продакшн)
$container = require __DIR__ . '/../config/container.php';

// Сохраняем контейнер в глобальную переменную
$GLOBALS['TEST_CONTAINER'] = $container;