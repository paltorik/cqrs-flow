<?php

namespace Paltorik\CqrsFlow;



use Paltorik\CqrsFlow\Contracts\QueryBusInterface;
use Paltorik\CqrsFlow\Contracts\QueryHandlerInterface;
use Paltorik\CqrsFlow\Contracts\QueryInterface;
use Paltorik\CqrsFlow\Contracts\RepositoryMapInterface;

/**
 * @template TCommand of QueryInterface<TResult>
 * @template TResult
 * @implements QueryBusInterface<TCommand, TResult>
 */
class AdvancedQueryBus implements QueryBusInterface
{
    private const string TYPE = 'queries';
    /**
     * @var array<class-string<QueryInterface>, QueryHandlerInterface>
     */
    protected array $handlers = [];

    public function __construct(protected MiddlewarePipeline $pipeline, protected RepositoryMapInterface $repository)
    {
    }

    /**
     * @param QueryInterface<TResult> $query
     * @return TResult
     */
    public function ask(QueryInterface $query): mixed
    {
        $handler = $this->repository->getHandlerFor(self::TYPE, $query);

        $handler = is_string($handler['class']) ? app($handler['class']) : $handler['class'];
        $finalHandler = fn(QueryInterface $query) => $handler->handle($query);

        return $this->pipeline->process($query, $finalHandler);
    }
}
