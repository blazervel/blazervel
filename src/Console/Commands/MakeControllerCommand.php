<?php

namespace Blazervel\Blazervel\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;

class MakeControllerCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'blazervel:make';

    protected $description = 'Create a new Blazervel Controller';

    protected $type = 'Blazervel Controller';

    protected function getStub()
    {
        return $this->resolveStubPath(
            "/../stubs/controller.stub"
        );
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Http\Blazervel";
    }
}
