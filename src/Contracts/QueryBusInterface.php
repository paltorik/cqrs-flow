<?php

namespace Paltorik\CqrsFlow\Contracts;

/**
 * Interface for the Query Bus.
 *
 * @template TCommand of QueryInterface<TResult>
 * @template TResult
 *
 * @author Paltorik
 */
interface QueryBusInterface
{
    /**
     * Execute a query.
     *
     * @param QueryInterface<TResult> $query The query to execute.
     * @return TResult The result of the query execution.
     */
    public function ask(QueryInterface $query): mixed;
}
