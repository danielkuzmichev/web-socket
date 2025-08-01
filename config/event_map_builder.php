<?php

use App\Core\Attribute\Event;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

function build_event_map(string $pattern): array
{
    $finder = new Finder();
    $finder->files()->in($pattern)->name('*.php');

    $map = [];

    $baseDir = realpath(__DIR__ . '/../src');

    foreach ($finder as $file) {
        $path = $file->getRealPath();
        require_once $path;

        $relativePath = str_replace([$baseDir . DIRECTORY_SEPARATOR, '.php'], '', $path);

        $className = 'App\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        if (!class_exists($className)) {
            continue;
        }

        $refClass = new ReflectionClass($className);
        $attributes = $refClass->getAttributes(Event::class);

        if (!empty($attributes)) {
            /** @var Event $eventAttr */
            $eventAttr = $attributes[0]->newInstance();
            $map[$eventAttr->key] = $className;
        }
    }

    return $map;
}
