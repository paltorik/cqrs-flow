<?php

namespace Paltorik\CqrsFlow\Contracts;

/**
 * Interface for middleware components.
 *
 * @author Paltorik
 */
interface MiddlewareInterface
{
    /**
     * Handle a command or query.
     *
     * @param CommandInterface|QueryInterface $command The command or query to process.
     * @param callable $next The next middleware or handler in the pipeline.
     * @return mixed The result of the processing.
     */
    public function handle(CommandInterface|QueryInterface $command, callable $next): mixed;
}
