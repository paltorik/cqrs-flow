<?php

namespace Paltorik\CqrsFlow\Loaders;



use Paltorik\CqrsFlow\Contracts\LoaderMapInterface;

class CartMapLoader implements LoaderMapInterface
{

    public  string $mapPath;

    public function __construct()
    {
        $this->mapPath = 'bootstrap/cache/' . config('commands.cache_file', 'command_map.php');
    }

    public function load(): array
    {
        if (!file_exists($this->mapPath)) {
            return [];
        }

        return require $this->mapPath;
    }
}
