<?php

namespace Paltorik\CqrsFlow\Contracts;

interface CommandPathProviderInterface
{
    /**
     * Возвращает массив путей для поиска команд
     */
    public function getCommandPaths(): array;
}
