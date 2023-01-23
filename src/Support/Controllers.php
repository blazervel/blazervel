<?php

namespace Blazervel\Blazervel\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class Controllers
{
    public static function dir(): string
    {
        return 'app/Http/Blazervel';
    }

    public static function namespace(): string
    {
        return (new Collection(explode('/', static::dir())))
                    ->map(fn ($slug) => Str::ucfirst(Str::camel($slug)))
                    ->join('\\');
    }

    public static function view(string $controllerClass): string
    {
        return collect(explode('\\', Str::remove(static::namespace() . '\\', $controllerClass)))->join('/') . '.tsx';
    }

    public static function configDefaults(string $controllerClass): array
    {
        $namespace = collect(explode('\\', Str::remove(static::namespace() . '\\', $controllerClass)));
        $name = $namespace->join('/');

        return [
            'name' => $name,
            'view' => "/{$name}", //"/{$name}.tsx",
            'route' => $namespace->map(fn ($s) => Str::snake($s, '-'))->join('/'),
        ];
    }

    public static function urlRoute(string $url, string $method = 'GET')
    {
        return (
            app('router')
                ->getRoutes()
                ->match(
                    app('request')
                        ->create($url, $method)
                )
        );
    }

    public static function urlParams(string $url, string $method = 'GET'): array
    {
        $route = static::urlRoute($url, $method);

        return $route->parameters;
    }

    public static function urlAction(string $url, string $method = 'GET'): string
    {
        $route = static::urlRoute($url, $method);
        $actionClass = $route->action['controller'];

        return static::actionKey($actionClass);
    }

    public static function actionComponent(string $action): string
    {
        $component = $action;
        $component = Str::replace('-', '/', $component);
        $component = "blazervel/{$component}";

        return $component;
    }

    public static function keyClass(string $classKey): string
    {
        // Support blazervel package actions
        if (Str::startsWith($classKey, 'blazervel-')) {
            $actionsNamespace = '';
        } else {
            $actionsNamespace = static::dir();
            $actionsNamespace = explode('/', $actionsNamespace);
            $actionsNamespace = collect($actionsNamespace)->map(fn ($an) => Str::ucfirst(Str::camel($an)))->join('\\');
            $actionsNamespace = "\\{$actionsNamespace}";
        }

        $actionClass = explode('-', $classKey);
        $actionClass = collect($actionClass)->map(fn ($ac) => Str::ucfirst(Str::camel($ac)))->join('\\');
        $actionClass = "{$actionsNamespace}\\{$actionClass}";

        return $actionClass;
    }

    public static function keyAction(string $actionKey): string
    {
        // Support blazervel package actions
        if (Str::startsWith($actionKey, 'blazervel-')) {
            $actionsNamespace = '';
        } else {
            $actionsNamespace = static::dir();
            $actionsNamespace = explode('/', $actionsNamespace);
            $actionsNamespace = collect($actionsNamespace)->map(fn ($an) => Str::ucfirst(Str::camel($an)))->join('\\');
            $actionsNamespace = "\\{$actionsNamespace}";
        }

        $actionClass = explode('-', $actionKey);
        $actionClass = collect($actionClass)->map(fn ($ac) => Str::ucfirst(Str::camel($ac)))->join('\\');
        $actionClass = "{$actionsNamespace}\\{$actionClass}";

        return $actionClass;
    }

    public static function actionKey(string $action): string
    {
        $key = Str::remove(Actions::namespace() . '\\', $action);
        $key = explode('\\', $key);
        $key = collect($key)->map(fn ($key) => Str::camel($key));

        return $key->join('-');
    }

    public static function directories(): array
    {
        $directory = static::dir();
        $directories = Finder::create()->in($directory)->directories()->sortByName();

        return (new Collection($directories))
                    ->map(fn ($dir) => Str::remove(base_path() . '/', $dir->getPathname()))
                    ->all();
    }

    public static function classes(): Collection
    {
        $actionsDir       = static::dir();
        $actionsNamespace = static::namespace();
        $classNames       = [];
        $files            = (new Filesystem)->allFiles(base_path($actionsDir));

        foreach ($files as $file) {
            $path      = $file->getPathName();
            $className = explode("{$actionsDir}/", $path)[1];
            $className = Str::remove('.php', $className);
            $className = Str::replace('/', '\\', $className);
            $className = "{$actionsNamespace}\\{$className}";

            $classNames[$className] = $path;
        }

        return collect($classNames);
    }

    public static function anonymousClasses(): Collection
    {
        $actions = [];

        foreach (static::classes() as $className => $path) {
            if (gettype(
                $class = require($path)
            ) !== 'object') {
                continue;
            }

            $class = get_class($class);

            if (! Str::contains($class, '@anonymous')) {
                continue;
            }

            $actions[$className] = $class;
        }

        return new Collection($actions);
    }
}