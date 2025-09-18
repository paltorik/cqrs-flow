<?php

namespace Paltorik\CqrsFlow;

use Paltorik\CqrsFlow\Contracts\CommandHandlerInterface;
use Paltorik\CqrsFlow\Contracts\LoaderMapInterface;
use Paltorik\CqrsFlow\Contracts\RepositoryMapInterface;
use RuntimeException;

class CommandRepository implements RepositoryMapInterface
{
    protected ?array $map=null;

    public function __construct(protected LoaderMapInterface $loader)
    {
    }

    public function getHandlerFor(string $type,object $command): array
    {
        $name = get_class($command);
        return $this->getMap()[$type][$name] ?? throw new RuntimeException("Handler not found for $name");
    }


    /**
     * @param class-string<TCommand> $commandClass
     * @param CommandHandlerInterface<TCommand, TResult> $handler
     */
    public function registerHandler(string $commandClass, CommandHandlerInterface $handler, bool $inTransaction): void
    {
        $this->map[$commandClass]['class'] = $handler;
        $this->map[$commandClass]['transaction'] = $inTransaction;
    }

    private function getMap(): ?array
    {
        if ($this->map==null) {
            $this->map = $this->loader->load();
        }
        return $this->map;
    }
}
