<?php

namespace Paltorik\CqrsFlow;

class CommandPathRegistry
{
    protected static array $paths = [];

    /**
     * Регистрирует пути поиска команд
     */
    public static function register(array $paths): void
    {
        static::$paths = array_merge(static::$paths, $paths);
    }

    /**
     * Возвращает все зарегистрированные пути
     */
    public static function getPaths(): array
    {
        return array_unique(static::$paths);
    }

    /**
     * Очищает реестр (полезно для тестов)
     */
    public static function clear(): void
    {
        static::$paths = [];
    }
}
