<?php

namespace App\Core\EventMap;

use App\Core\Attribute\Event;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

class EventMapBuilder
{
    public static function build(string $srcDir): array
    {
        $finder = new Finder();
        $finder->files()->in($srcDir)->name('*.php');

        $map = [];

        $baseDir = realpath(__DIR__ . '/../../'); // корень src, скорректируй под свой проект

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            require_once $path;

            // Относительный путь от корня src
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
}
