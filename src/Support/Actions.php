<?php

namespace Blazervel\Blazervel\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;

class Actions
{
    public static function dir(): string
    {
        return 'app/Actions/Blazervel';
    }

    public static function namespace(): string
    {
        return
            (new Collection(explode('/', static::dir())))
                ->map(fn ($slug) => Str::ucfirst(Str::camel($slug)))
                ->join('\\');
    }

    protected static function getConstant(string $action, string $constant, string|array $default): string|array
    {
        $value = null;

        if (defined("{$action}::{$constant}")) {
            $value = $action::$constant;
        }

        return $value ?: $default;
    }

    public static function meta(string $action): object
    {
        $namespace = static::namespace();
        $explodedAction = explode('\\', Str::remove($namespace . '\\', $action));
        $actionFromNamespace = Str::singular(array_pop($explodedAction));
        $modelFromNamespace = Str::camel(array_pop($explodedAction));
        $modelFromNamespace = class_exists("App\\Models\\{$modelFromNamespace}") ? $modelFromNamespace : null;
        $routePath = collect($explodedAction)->map(fn ($p) => Str::snake($p, '-'))->join('/');
        $commonActionMethods = [
            'show' => 'get',
            'index' => 'get',
            'create' => 'get',
            'edit' => 'get',
            'update' => 'put',
            'store' => 'post',
            'delete' => 'delete',
            'destroy' => 'delete',
        ];
        $routeMethod = $commonActionMethods[Str::lower($actionFromNamespace)] ?: 'get';
        $actionSlug = Str::lower(Str::snake(class_basename($action), '-'));

        $name = Str::remove($namespace . '\\', $action);
        $name = explode('\\', $name);
        $name = collect($name)->map(fn ($p) => Str::snake($p, '-'))->join('.');

        $route = Str::replace('.', '/', $name);

        if (in_array($actionSlug, array_keys($commonActionMethods))) {
            $route = rtrim($route, $actionSlug);
        }

        if (in_array($actionSlug, [
            'show',
            'update',
            'edit',
            'destroy',
            'delete',
        ])) {
            $dirName = Str::singular(basename($route));

            if (class_exists('App\\Models\\'.Str::camel(Str::ucfirst($dirName)))) {
                $lookupKeyName = Str::replace('-', '_', $dirName);
            }

            $route = "$route/\{$lookupKeyName\}";
        }

        // Smartly add other params to url based on action parameters
        // e.g. teams/users/show => teams/{team}/users/{user}
        $parameters = (new ReflectionClass($action))->getMethod('__invoke')->getParameters();
        $parameters = (
            collect($parameters)
                ->filter(fn ($p) => ! in_array($p->getType()->getName(), [
                    'Illuminate\Http\Request',
                ]))
                ->map(fn ($p) => $p->getName())
        );

        $parameters->each(fn ($p) => $route = Str::replace(($s = Str::plural($p)), "$s/\{$p\}", $route));

        return (object) [
            'name' => $actionFromNamespace,
            'slug' => Str::snake($actionFromNamespace, '-'),
            'model' => $modelFromNamespace,
            'modelParamater' => '{' . Str::snake($modelFromNamespace, '_') . '}',
            'route' => (object) [
                'middleware' => static::getConstant($action, 'route', ['web']),
                'method' => static::getConstant($action, 'method', $routeMethod),
                'path' => static::getConstant($action, 'route', $routePath),
                'name' => Str::replace('/', '.', $routePath),
            ],
        ];
    }

    public static function actionParams(string $action): array
    {
        $except = [
            'Illuminate\Http\Request',
        ];

        $parameters = (new ReflectionClass($action))->getMethod('__invoke')->getParameters();

        return
            collect($parameters)
                ->filter(fn ($p) => ! in_array($p->getType()->getName(), $except))
                ->map(fn ($p) => $p->getName())
                ->all();
    }

    public static function registerRoutes(): void
    {
        $routes = static::classes()->map(fn ($fp, $action) => static::meta($action));

        $routes->each(function ($route) {
            $router = Route::middleware($route['middleware']);
            $method = $route['method'];

            $router->$method(
                $route['route'],
                $route['action']
            )->name(
                $route['name']
            );
        });
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
        $key = Str::remove(Actions::namespace().'\\', $action);
        $key = explode('\\', $key);
        $key = collect($key)->map(fn ($key) => Str::camel($key));

        return $key->join('-');
    }

    public static function classes(): Collection
    {
        $actionsDir = static::dir();
        $actionsNamespace = static::namespace();
        $classNames = [];
        $files = (new Filesystem)->allFiles(base_path($actionsDir));

        foreach ($files as $file) {
            $path = $file->getPathName();
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
