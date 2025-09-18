<?php

namespace Paltorik\CqrsFlow\Attributes;
use Attribute;

/**
 * Attribute to mark a command as asynchronous.
 *
 * @author Paltorik
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsyncCommand
{
    /**
     * @param string|null $queue The name of the queue to use for the command.
     * @param int|null $delaySeconds The delay in seconds before the command is processed.
     */
    public function __construct(
        public ?string $queue = null,
        public ?int $delaySeconds = null,
    ) {}
}
