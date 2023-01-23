<?php

namespace Blazervel\Blazervel\Providers;

use Blazervel\Blazervel\Console\Commands\MakeActionCommand;
use Blazervel\Blazervel\Console\Commands\BuildCommand;
use Blazervel\Blazervel\Console\Commands\MakeAnonymousActionCommand;
use Blazervel\Blazervel\Console\Commands\MakeControllerCommand;
use Blazervel\Blazervel\Support\Actions;
use Blazervel\Blazervel\Support\ApiRoutes;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
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
            ->loadViews()
            ->loadCommands()
            ->loadRoutes();
    }

    private function loadCommands(): self
    {
        if (! $this->app->runningInConsole()) {
            return $this;
        }

        $this->commands([
            MakeControllerCommand::class,
            MakeActionCommand::class,
            MakeAnonymousActionCommand::class,
            BuildCommand::class
        ]);

        return $this;
    }

    private function loadViews(): self
    {
        $this->loadViewsFrom(
            static::path('resources/views'),
            'blazervel'
        );

        return $this;
    }

    private function registerAnonymousClassAliases(): self
    {
        $this->app->booting(function ($app) {
            $loader = AliasLoader::getInstance();

            collect([
                'Blazervel\\Action'           => \Blazervel\Blazervel\Action::class,
                'Blazervel\\WithModelActions' => \Blazervel\Blazervel\WithModelActions::class,
                'B'                           => \Blazervel\Blazervel\Support\Helpers::class,
            ])->map(fn ($class, $namespace) => (
                $loader->alias(
                    $namespace,
                    $class
                )
            ));

            if (Config::get('blazervel.actions.anonymous_classes', true)) {
                Actions::anonymousClasses()->map(fn ($class, $namespace) => (
                    $loader->alias(
                        $namespace,
                        $class
                    )
                ));
            }
        });

        return $this;
    }

    private function loadRoutes(): self
    {
        ApiRoutes::register();

        return $this;
    }

    static function path(string ...$path): string
    {
        return join('/', [
            Str::remove('src/Providers', __DIR__),
            ...$path
        ]);
    }
}