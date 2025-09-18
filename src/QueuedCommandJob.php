<?php

namespace Paltorik\CqrsFlow;

use Paltorik\CqrsFlow\Attributes\AsyncCommand;
use Paltorik\CqrsFlow\Contracts\CommandHandlerInterface;
use Paltorik\CqrsFlow\Contracts\CommandInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Represents a queued job for handling commands asynchronously.
 *
 * @author Paltorik
 */
class QueuedCommandJob implements ShouldQueue
{
    use Queueable;

    /**
     * @var CommandHandlerInterface The handler for the command.
     */
    private CommandHandlerInterface $handler;

    /**
     * @var CommandInterface The command to be processed.
     */
    private CommandInterface $command;

    /**
     * @var AsyncCommand Configuration for the asynchronous command.
     */
    private AsyncCommand $commandSetting;

    /**
     * The middleware the job should be dispatched through.
     *
     * @var array
     */
    public $middleware = [];

    /**
     * Constructor for the QueuedCommandJob.
     *
     * @param CommandHandlerInterface $handler The handler for the command.
     * @param CommandInterface $command The command to be processed.
     * @param AsyncCommand $commandSetting Configuration for the asynchronous command.
     * @param array $middleware Middleware components for the job.
     */
    public function __construct(
        CommandHandlerInterface $handler,
        CommandInterface $command,
        AsyncCommand $commandSetting,
        array $middleware = [],
    ) {
        $this->handler = $handler;
        $this->command = $command;
        $this->commandSetting = $commandSetting;
        $this->middleware = $middleware;

        $this->queue = $commandSetting->queue;
        $this->delay = $commandSetting->delaySeconds;
    }

    /**
     * Handle the queued job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->handler->handle($this->command);
    }

    /**
     * Get the middleware components for the job.
     *
     * @return array The middleware components.
     */
    public function middleware(): array
    {
        return $this->middleware;
    }
}
