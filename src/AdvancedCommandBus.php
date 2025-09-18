<?php

namespace Paltorik\CqrsFlow;


use Paltorik\CqrsFlow\Attributes\AsyncCommand;
use Paltorik\CqrsFlow\Contracts\CommandBusInterface;
use Paltorik\CqrsFlow\Contracts\CommandHandlerInterface;
use Paltorik\CqrsFlow\Contracts\CommandInterface;
use Paltorik\CqrsFlow\Contracts\CommandRollbackHandlerInterface;
use Paltorik\CqrsFlow\Contracts\RepositoryMapInterface;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * @template TCommand of CommandInterface<TResult>
 * @template TResult
 * @implements CommandBusInterface<TCommand, TResult>
 */
class AdvancedCommandBus implements CommandBusInterface
{
    private const string TYPE = 'commands';
    /**
     * Кэш результата ReflectionAttribute для класса команды.
     * [commandClass => AsyncCommand|null]
     * @var array<string, AsyncCommand|null>
     */
    protected array $asyncAttributeCache = [];


    /**
     * @param MiddlewarePipeline $pipeline
     * @param Dispatcher $dispatcher
     * @param RepositoryMapInterface $repository
     */
    public function __construct(
        protected MiddlewarePipeline $pipeline,
        protected Dispatcher $dispatcher,
        protected RepositoryMapInterface $repository
    ) {
    }

    /**
     * @param CommandInterface<TResult> $command
     * @return TResult
     * @throws \Throwable
     */
    public function dispatch(CommandInterface $command, bool $async = false): mixed
    {
        $asyncAttr = $this->getAsyncAttribute(get_class($command));
        $handler = $this->makeHandler($command);

        if ($asyncAttr !== null || $async) {
            return $this->dispatcher->dispatch(
                new QueuedCommandJob(
                    $handler,
                    $command,
                    $asyncAttr,
                    $this->pipeline->getMiddleware()
                )
            );
        }

        // Явно захватываем $handler в замыкание через use
        $finalHandler = $this->useTransaction($command)
            ? function (CommandInterface $command) use ($handler) {
                return DB::transaction(function () use ($command, $handler) {
                    return $handler->handle($command);
                });
            }
            : function (CommandInterface $command) use ($handler) {
                return $handler->handle($command);
            };

        try {
            return $this->pipeline->process($command, $finalHandler);
        } catch (\Throwable $throwable) {
            if ($handler instanceof CommandRollbackHandlerInterface) {
                $handler->rollback($command);
            }
            throw $throwable;
        }
    }

    /**
     * @param CommandInterface $command
     * @return CommandHandlerInterface<TCommand, TResult>
     */
    protected function makeHandler(CommandInterface $command): CommandHandlerInterface
    {
        /** @var CommandHandlerInterface<TCommand, TResult> $handler */
        $handlerArr = $this->repository->getHandlerFor(self::TYPE, $command);
        return is_string($handlerArr['class']) ? app($handlerArr['class']) : $handlerArr['class'];
    }

    /**
     * @param CommandInterface $command
     * @return bool
     */
    protected function useTransaction(CommandInterface $command): bool
    {
        return $handlerArr = $this->repository->getHandlerFor(self::TYPE, $command)['transaction'] ?? false;
    }

    protected function getAsyncAttribute(string $commandClass): ?AsyncCommand
    {
        if (array_key_exists($commandClass, $this->asyncAttributeCache)) {
            return $this->asyncAttributeCache[$commandClass];
        }

        $reflection = new ReflectionClass($commandClass);

        $attrs = $reflection->getAttributes(AsyncCommand::class);
        if (count($attrs) === 0) {
            return null;
        }
        $asyncAttr = $attrs[0]->newInstance() ?? null;

        $this->asyncAttributeCache[$commandClass] = $asyncAttr;

        return $asyncAttr;
    }

}
