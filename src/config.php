<?php

return [
    'paths' => [
        'app/Processes/Handlers',
        'app/Processes/Queries',
    ],
    'cache_file' => 'command_map.php',
    'load_driver' => \Paltorik\CqrsFlow\Loaders\CartMapLoader::class
];
