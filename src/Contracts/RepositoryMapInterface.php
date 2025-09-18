<?php

namespace Paltorik\CqrsFlow\Contracts;

interface RepositoryMapInterface
{
    public function getHandlerFor(string $type,object $command): array;
    /**
     * @param class-string<TCommand> $commandClass
     * @param CommandHandlerInterface<TCommand, TResult> $handler
     * @param bool $inTransaction
     */
    public function registerHandler(string $commandClass, CommandHandlerInterface $handler, bool $inTransaction): void;
}
