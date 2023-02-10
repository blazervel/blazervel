<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;

class MakeActionCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'blazervel:make';

    protected $description = 'Create a new Blazervel Action';

    protected $type = 'Blazervel Action';

    protected bool $anonymous = false;

    protected bool $inertia = false;

    protected function getStub()
    {
        $stubPath = '/../stubs/action.stub';

        if ($this->inertia === true) {
            $stubPath = '/../stubs/inertia-action.stub';
        } elseif ($this->anonymous === true) {
            $stubPath = '/../stubs/anonymous-action.stub';
        }

        return $this->resolveStubPath($stubPath);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Actions\Blazervel";
    }
}
