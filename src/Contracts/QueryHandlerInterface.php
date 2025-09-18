<?php

namespace Paltorik\CqrsFlow\Contracts;

/**
 * Interface for handling queries.
 *
 * @template TCommand of QueryInterface<TResult>
 * @template TResult
 *
 * @author Paltorik
 */
interface QueryHandlerInterface
{
    /**
     * Handle a query.
     *
     * @param QueryInterface<TResult> $command The query to handle.
     * @return TResult The result of the query execution.
     */
    public function handle(QueryInterface $command): mixed;
}
