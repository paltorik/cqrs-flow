<?php

namespace Paltorik\CqrsFlow\Contracts;

interface LoaderMapInterface
{
    /** @return array<class-string, class-string> */
    public function load(): array;
}
