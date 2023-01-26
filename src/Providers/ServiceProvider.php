<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Providers;

use Blazervel\Blazervel\Action;
use Blazervel\Blazervel\Console\Commands\MakeActionCommand;
use Blazervel\Blazervel\Console\Commands\MakeAnonymousActionCommand;
use Blazervel\Blazervel\Support\Actions;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->registerAnonymousClassAliases();
    }

    public function boot()
    {
        $this
            ->loadCommands()
            ->loadRoutes();
    }

    private function loadCommands(): self
    {
        if (! $this->app->runningInConsole()) {
            return $this;
        }

        $this->commands([
            MakeActionCommand::class,
            MakeAnonymousActionCommand::class,
        ]);

        return $this;
    }

    private function registerAnonymousClassAliases(): self
    {
        $anonymousActionClasses = Actions::anonymousClasses();

        $this->app->booting(function ($app) use ($anonymousActionClasses) {
            $loader = AliasLoader::getInstance();

            $loader->alias('BlazervelAction', Action::class);

            $anonymousActionClasses->map(fn ($class, $namespace) => (
                $loader->alias($namespace, $class)
            ));
        });

        return $this;
    }

    private function loadRoutes(): self
    {
        Actions::registerRoutes();

        return $this;
    }

    public static function path(string ...$path): string
    {
        return implode('/', [
            Str::remove('src/Providers', __DIR__),
            ...$path,
        ]);
    }
}
