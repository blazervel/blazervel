<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Providers;

use Blazervel\Blazervel\Console\Commands\MakeActionCommand;
use Blazervel\Blazervel\Console\Commands\MakeAnonymousActionCommand;
use Blazervel\Blazervel\Console\Commands\MakeInertiaActionCommand;
use Blazervel\Blazervel\Console\Commands\TranspileAnonymousActionsCommand;
use Blazervel\Blazervel\Support\Actions;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
            MakeInertiaActionCommand::class,
            MakeAnonymousActionCommand::class,
            TranspileAnonymousActionsCommand::class,
        ]);

        return $this;
    }

    private function registerAnonymousClassAliases(): self
    {
        $anonymousActionClasses = Actions::anonymousClasses();

        $this->app->booting(function ($app) use ($anonymousActionClasses) {
            $loader = AliasLoader::getInstance();

            $t = $anonymousActionClasses
                ->map(fn ($class, $namespace) => (
                    ($object = require_once($class)) !== true
                        ? [$namespace => $object]
                        : null
                ))
                ->whereNotNull()
                ->collapse()
                ->each(fn ($object, $namespace) => (
                    $loader->alias($namespace, $object::class)
                ))
                ->map(fn ($object, $namespace) => $object::class);

            // dd($t->map(fn ($class) => class_exists("Blazervel\Blazervel\Action@anonymous\x00/Users/joshuaanderton/Sites/blazervel/app/Actions/Blazervel/" . (basename(Str::remove('.php', $class))) . '.php')));
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
