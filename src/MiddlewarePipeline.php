<?php

namespace Paltorik\CqrsFlow;


use Paltorik\CqrsFlow\Contracts\CommandInterface;
use Paltorik\CqrsFlow\Contracts\MiddlewareInterface;
use Paltorik\CqrsFlow\Contracts\QueryInterface;

/**
 * Middleware pipeline for processing commands and queries.
 *
 * @author Paltorik
 */
class MiddlewarePipeline
{
    /**
     * @var array<MiddlewareInterface>
     * Array of middleware components.
     */
    protected array $middleware = [];

    /**
     * Add a middleware component to the pipeline.
     *
     * @param MiddlewareInterface $middleware The middleware to add.
     * @return self
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Process a command or query through the middleware pipeline.
     *
     * @param CommandInterface|QueryInterface $command The command or query to process.
     * @param callable $finalHandler The final handler to execute after middleware.
     * @return mixed The result of the processing.
     */
    public function process(CommandInterface|QueryInterface $command, callable $finalHandler): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            fn($next, MiddlewareInterface $middleware) => fn($command) => $middleware->handle($command, $next),
            $finalHandler
        );

        return $pipeline($command);
    }

    /**
     * Get the list of middleware components.
     *
     * @return array<MiddlewareInterface> The middleware components.
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
