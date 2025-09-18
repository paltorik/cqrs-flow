<?php

namespace Paltorik\CqrsFlow\Loaders;

use Paltorik\CqrsFlow\Attributes\AsCommandHandler;
use Paltorik\CqrsFlow\Attributes\AsQueryHandler;
use Paltorik\CqrsFlow\Contracts\LoaderMapInterface;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ReflectionMapLoader implements LoaderMapInterface
{
    protected array $paths;
    public function __construct()
    {
        $this->paths = config('flow.paths', []);

    }

    public function load(): array
    {
        $paths = $this->paths;
        $commands = [];
        $queries = [];
        foreach ($paths as $basePath) {
            if (!is_dir(base_path($basePath))) {
                continue;
            }
            foreach (File::allFiles(base_path($basePath)) as $file) {
                $class = $this->get_class_from_file($file->getPathname());
                if (!class_exists($class)) {
                    continue;
                }
                $reflection = new ReflectionClass($class);

                foreach (
                    $reflection->getAttributes(
                        AsCommandHandler::class
                    ) as $attr
                ) {
                    /** @var AsCommandHandler $attrInstance */
                    $attrInstance = $attr->newInstance();
                    $commands[$attrInstance->command]['class'] = $class;
                    $commands[$attrInstance->command]['transaction'] = $attrInstance->inTransaction;
                }
                foreach ($reflection->getAttributes(AsQueryHandler::class) as $attr) {
                    /** @var AsQueryHandler $attrInstance */
                    $attrInstance = $attr->newInstance();
                    $queries[$attrInstance->command]['class'] = $class;
                }
            }
        }
        return [
            'commands' => $commands,
            'queries' => $queries,
        ];
    }

    private function get_class_from_file(string $file): ?string
    {
        $content = file_get_contents($file);

        if (!preg_match('/namespace\s+(.+?);/', $content, $namespaceMatches)) {
            return null;
        }
        if (!preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return null;
        }

        return $namespaceMatches[1] . '\\' . $classMatches[1];
    }
}
