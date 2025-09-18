<?php

namespace Paltorik\CqrsFlow\Attributes;

use Attribute;

/**
 * Attribute to mark a class as a query handler.
 *
 * @author Paltorik
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsQueryHandler
{
    /**
     * @param string $command The query class that this handler processes.
     */
    public function __construct(public string $command) {}
}
