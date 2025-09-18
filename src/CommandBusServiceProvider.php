<?php

namespace Paltorik\CqrsFlow;


use Paltorik\CqrsFlow\Contracts\CommandBusInterface;
use Paltorik\CqrsFlow\Loaders\CartMapLoader;
use Paltorik\CqrsFlow\Loaders\CacheMapLoader;
use Paltorik\CqrsFlow\Contracts\QueryBusInterface;
use Paltorik\CqrsFlow\Contracts\RepositoryMapInterface;
use Paltorik\CqrsFlow\Loaders\ReflectionMapLoader;
use Paltorik\CqrsFlow\Middleware\LoggingMiddleware;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class CommandBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'flow');

        $pipeline = (new MiddlewarePipeline())
            ->pipe(new LoggingMiddleware());

        $this->app->bind(RepositoryMapInterface::class, function () use ($pipeline) {
            return new CommandRepository(new ReflectionMapLoader());
        });

        $this->app->singleton(CommandBusInterface::class, function ($app) use ($pipeline) {
            return new AdvancedCommandBus(
                $pipeline,
                $app->make(Dispatcher::class),
                $app->make(RepositoryMapInterface::class)
            );
        });
        $this->app->singleton(
            QueryBusInterface::class,
            function ($app) use ($pipeline) {
                return new AdvancedQueryBus($pipeline, $app->make(RepositoryMapInterface::class));
            }
        );
    }

    public function boot(): void
    {
        $this->commands(GenerateCommandMap::class);

        // Публикуем конфигурацию для возможности расширения
        $this->publishes([
            __DIR__ . '/config.php' => config_path('flow.php'),
        ], 'flow');

        // ПОСЛЕ загрузки всех провайдеров объединяем пути
        $this->app->booted(function () {
            $this->mergeAllCommandPaths();
        });
    }

    /**
     * Объединяет все пути команд после загрузки всех провайдеров
     */
    protected function mergeAllCommandPaths(): void
    {
        $basePaths = config('flow.paths', []);
        $additionalPaths = CommandPathRegistry::getPaths();

        $allPaths = array_unique(array_merge($basePaths, $additionalPaths));

        config(['flow.paths' => $allPaths]);
    }
}
