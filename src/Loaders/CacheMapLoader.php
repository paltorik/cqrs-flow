<?php

namespace Paltorik\CqrsFlow\Loaders;

use Paltorik\CqrsFlow\Contracts\LoaderMapInterface;
use Illuminate\Support\Facades\Cache;

class CacheMapLoader implements LoaderMapInterface
{

    public function load(): array
    {
        return Cache::rememberForever('command_handlers_map', function () {
            $reflectionLoader = new ReflectionMapLoader();
            return $reflectionLoader->load();
        });
    }
}
