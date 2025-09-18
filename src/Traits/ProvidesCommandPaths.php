<?php

namespace Paltorik\CqrsFlow\Traits;

trait ProvidesCommandPaths
{
    /**
     * Регистрирует пути поиска команд для CommandBus
     */
    protected function registerCommandPaths(array $paths): void
    {
        $this->app->tag(['command-paths' => $paths], 'flow.command-paths');
    }

    /**
     * Получает пути команд для данного провайдера
     */
    public function getCommandPaths(): array
    {
        return $this->commandPaths ?? [];
    }
}
