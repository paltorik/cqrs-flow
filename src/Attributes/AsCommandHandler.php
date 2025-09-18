<?php

namespace Paltorik\CqrsFlow\Attributes;

use Attribute;

/**
 * Attribute to mark a class as a command handler.
 *
 * @author Paltorik
 */

#[Attribute(Attribute::TARGET_CLASS)]
class AsCommandHandler
{
    public function __construct(public string $command, public bool $inTransaction = true)
    {
    }
}
