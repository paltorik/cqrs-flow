<?php

namespace Paltorik\CqrsFlow\Middleware;

use Paltorik\CqrsFlow\Contracts\CommandInterface;
use Paltorik\CqrsFlow\Contracts\QueryInterface;
use Paltorik\CqrsFlow\Contracts\MiddlewareInterface;
use Illuminate\Support\Facades\Log;

/**
 * Middleware for logging the handling of commands and queries.
 *
 * @author Paltorik
 */
class LoggingMiddleware implements MiddlewareInterface
{
    /**
     * Handle a command or query and log its processing.
     *
     * @param CommandInterface|QueryInterface $command The command or query to process.
     * @param callable $next The next middleware or handler in the pipeline.
     * @return mixed The result of the processing.
     * @throws \Exception
     * @throws \Exception
     */
    public function handle(CommandInterface|QueryInterface $command, callable $next): mixed
    {
        $commandClass = get_class($command);

        Log::error("Executing command: {$commandClass}", [
            'command_data' => $command
        ]);

        $startTime = microtime(true);

        try {
            $result = $next($command);

            $executionTime = microtime(true) - $startTime;
            Log::error("Command executed successfully: {$commandClass}", [
                'execution_time' => $executionTime
            ]);

            return $result;
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error("Command failed: {$commandClass}", [
                'execution_time' => $executionTime,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
