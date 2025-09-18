<?php

namespace Paltorik\CqrsFlow\Contracts;

/**
 * Interface for the Command Bus.
 *
 * @template TCommand of CommandInterface<TResult>
 * @template TResult
 *
 * @author Paltorik
 */
interface CommandBusInterface
{
    /**
     * Dispatch a command.
     * @param CommandInterface<TResult> $command
     * @param bool $async
     * @return TResult
     */
    public function dispatch(CommandInterface $command, bool $async = false): mixed;

}
