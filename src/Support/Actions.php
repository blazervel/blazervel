<?php

namespace Blazervel\Blazervel\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;

class Actions
{
    public static function dir(): string
    {
        return Config::get('blazervel.actions.actions_dir', 'app/Actions/Blazervel');
    }

    public static function namespace(): string
    {
        return (new Collection(explode('/', static::dir())))
                    ->map(fn ($slug) => Str::ucfirst(Str::camel($slug)))
                    ->join('\\');
    }

    public static function routes(): Collection
    {
        $webPrefix = 'App\\Actions\\Blazervel\\Http\\';
        $apiPrefix = 'App\\Actions\\Blazervel\\Api\\';
        $routedActions = static::classes()->filter(fn ($filePath, $class) => (
            Str::startsWith($class, $webPrefix) ||
            Str::startsWith($class, $apiPrefix)
        ));
        $middlewares = [
            $webPrefix => ['web'],
            $apiPrefix => ['api']
        ];
        $methods = [
            'show' => 'get',
            'index' => 'get',
            'create' => 'get',
            'edit' => 'get',
            'update' => 'put',
            'store' => 'post',
            'delete' => 'delete',
            'destroy' => 'delete'
        ];

        $routes = $routedActions->map(function ($filePath, $action) use ($webPrefix, $apiPrefix, $middlewares, $methods) {

            $lookupKeyName = 'id';

            $slug = Str::lower(class_basename($action));

            $method = $methods[$slug] ?? 'get';
            
            $middleware = Str::startsWith($action, $webPrefix)
                ? $middlewares[$webPrefix]
                : $middlewares[$apiPrefix];

            $name = Str::startsWith($action, $webPrefix)
                ? Str::remove($webPrefix, $action)
                : Str::remove($apiPrefix, $action);

            $name = explode('\\', $name);
            $name = collect($name)->map(fn ($p) => Str::snake($p, '-'))->join('.');

            if (! defined("{$action}::route") || ! ($path = $action::route ?? false)) {

                $path = Str::replace('.', '/', $name);

                if (in_array($slug, array_keys($methods))) {
                    $path = rtrim($path, $slug);
                }

                if (in_array($slug, [
                    'show',
                    'update',
                    'edit',
                    'destroy',
                    'delete',
                ])) {
                    $dirName = Str::singular(basename($path));

                    if (class_exists('App\\Models\\' . Str::camel(Str::ucfirst($dirName)))) {
                        $lookupKeyName = Str::replace('-', '_', $dirName);
                    }

                    $path = $path.'{'.$lookupKeyName.'}';
                }

                // Smartly add other params to url based on action parameters
                // e.g. teams/users/show => teams/{team}/users/{user}
                // $parameters = (new ReflectionClass($action))->getMethod('__invoke')->getParameters();
                // $parameters = (
                //     collect($parameters)
                //         ->filter(fn ($p) => ! in_array($p->getType()->getName(), [
                //             'Illuminate\Http\Request'
                //         ]))
                //         ->map(fn ($p) => $p->getName())
                // );
            }

            return compact('middleware', 'method', 'path', 'action', 'name');
        });

        return $routes;
    }

    public static function registerRoutes(): void
    {
        static::routes()->each(function ($route) {
            $router = Route::middleware($route['middleware']);
            $method = $route['method'];

            $router->$method(
                $route['path'],
                $route['action']
            )->name(
                $route['name']
            );
        });
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