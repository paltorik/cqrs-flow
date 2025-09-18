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
interface CommandHandlerInterface
{
    /**
     * Handle a command.
     *
     * @param CommandInterface<TResult> $command The command to handle.
     * @return TResult The result of the command execution.
     */
    public function handle(CommandInterface $command): mixed;
}
