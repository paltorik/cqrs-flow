<?php

namespace Paltorik\CqrsFlow;

use Paltorik\CqrsFlow\Contracts\Loaders\CartMapLoader;
use Paltorik\CqrsFlow\Contracts\Loaders\ReflectionMapLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateCommandMap extends Command
{
    protected $signature = 'command:map';
    protected $description = 'Генерация маппинга Command → Handler по атрибутам';

    public function handle(): int
    {

        $reflectionLoader = new ReflectionMapLoader();
        $outputPath = base_path((new CartMapLoader())->mapPath);
        File::put($outputPath, '<?php return ' . var_export($reflectionLoader->load(), true) . ';');

        $this->info("✔ Маппинг сгенерирован: " . $outputPath);
        return self::SUCCESS;
    }


}
