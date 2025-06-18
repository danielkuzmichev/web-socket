<?php

namespace Tests;

class TestContainerLocator
{
    private static $container;

    public static function set($container): void
    {
        self::$container = $container;
    }

    public static function get()
    {
        return self::$container;
    }
}
