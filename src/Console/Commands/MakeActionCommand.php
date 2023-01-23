<?php

namespace Blazervel\Blazervel\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;

class MakeActionCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'blazervel:make:action';

    protected $description = 'Create a new Blazervel Action';

    protected $type = 'Blazervel Action';

    protected bool $anonymous = false;

    protected function getStub()
    {
        if ($this->anonymous === true) {
            return $this->resolveStubPath(
                "/../stubs/anonymous-action.stub"
            );
        }

        return $this->resolveStubPath(
            "/../stubs/action.stub"
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
        return "{$rootNamespace}\Actions\Blazervel";
    }
}
