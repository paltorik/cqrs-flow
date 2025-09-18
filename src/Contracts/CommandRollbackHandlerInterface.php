<?php

namespace Paltorik\CqrsFlow\Contracts;

/**
 * Interface for handling commands.
 *
 * @template TCommand of CommandInterface<TResult>
 * @template TResult
 *
 * @author Paltorik
 */
interface CommandRollbackHandlerInterface
{
    /**
     * Handle a command.
     *
     * @param CommandInterface<TResult> $command The command to handle.
     * @return void The result of the command execution.
     */
    public function rollback(CommandInterface $command): void;
}