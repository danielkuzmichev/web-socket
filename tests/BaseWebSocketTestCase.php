<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class BaseWebSocketTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function getClient(): WebSocketClientDecorator
    {
        return new WebSocketClientDecorator();
    }

    public function getContainer(): mixed
    {
        return TestContainerLocator::get();
    }

    public function getFromContainer(string $class): mixed
    {
        return $this->getContainer()->get($class);
    }

    /**
     * Рекурсивно проверяет структуру массива
     *
     * @param array $data Проверяемые данные
     * @param array $structure Ожидаемая структура
     * @param string $path Текущий путь (для сообщений об ошибках)
     */
    protected function assertArrayStructure(
        array $data,
        array $structure,
        string $path = ''
    ): void {
        foreach ($structure as $key => $expected) {
            $currentPath = $path ? "$path.$key" : $key;

            // Если ключ числовой (0 => 'field'), проверяем только наличие поля
            $field = is_int($key) ? $expected : $key;

            $this->assertArrayHasKey(
                $field,
                $data,
                "Missing required field: $currentPath"
            );

            // Если значение не массив и не числовой ключ, проверяем значение
            if (!is_int($key) && !is_array($expected)) {
                $this->assertEquals(
                    $expected,
                    $data[$field],
                    "Invalid value for field: $currentPath"
                );
            }

            // Рекурсивная проверка вложенных массивов
            if (is_array($expected) && is_array($data[$field])) {
                $this->assertArrayStructure(
                    $data[$field],
                    $expected,
                    $currentPath
                );
            }
        }
    }
}
