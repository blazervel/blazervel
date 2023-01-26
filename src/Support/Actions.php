<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Blazervel\Blazervel\Objects\ActionMeta;
use ReflectionClass;
use ReflectionProperty;

class Actions
{
    public static function dir(): string
    {
        return 'app/Actions/Blazervel';
    }

    public static function dirNamespace(string $dir): string
    {
        return
            (new Collection(explode('/', $dir)))
                ->map(fn ($slug) => Str::ucfirst(Str::camel($slug)))
                ->join('\\');
    }
    
    /**
     * @return string[]
     */
    protected static function commonActionMethods(): array
    {
        return [
            'show' => 'get',
            'index' => 'get',
            'create' => 'get',
            'edit' => 'get',
            'update' => 'put',
            'store' => 'post',
            'delete' => 'delete',
            'destroy' => 'delete',
        ];
    }

    /**
     * @return string[]
     */
    protected static function commonLookupActions(): array
    {
        return [
            'show',
            'update',
            'edit',
            'destroy',
            'delete',
        ];
    }

    protected static function getPredefinedProperty(string $action, string $property, string|array $default = null): string|array|null
    {
        $properties = (new ReflectionClass($action))->getProperties(ReflectionProperty::IS_PROTECTED);
        
        $value = (
            collect($properties)
                ->filter(fn ($p) => $p->hasDefaultValue() && $p->getName() === $property)
                ->map(fn ($p) => $p->getDefaultValue())
                ->first()
        );

        return $value ?: $default;
    }

    public static function meta(string $action): ActionMeta
    {
        $dir = static::dir();
        $namespace = static::dirNamespace($dir);
        $commonActionMethods = static::commonActionMethods();
        $explodedAction = explode('\\', Str::remove($namespace . '\\', $action));

        $defaultRoute = collect($explodedAction)->map(fn ($p) => Str::snake($p, '-'))->join('/');
        $routeName = Str::replace('/', '.', $defaultRoute);
        $route = static::getPredefinedProperty($action, 'route');

        $actionName = Str::singular(array_pop($explodedAction));
        $actionSlug = Str::snake($actionName, '-');

        $modelClass = Str::singular(Str::ucfirst(Str::camel(array_pop($explodedAction))));
        $modelClass = "App\\Models\\{$modelClass}";
        $modelClass = class_exists($modelClass) ? $modelClass : null;

        $httpMethod = $commonActionMethods[Str::lower($actionName)] ?? null;

        if (! $route) {

            $route = $defaultRoute;

            if (in_array($actionSlug, array_keys($commonActionMethods))) {
                $route = rtrim($route, $actionSlug);
            }

            // Set route parameter for common lookup actions
            if (in_array($actionSlug, static::commonLookupActions())) {
                $lookupKeyName = 'id';

                if ($modelClass !== null) {
                    $lookupKeyName = Str::snake(class_basename($modelClass));
                }

                $route = $route.'{'.$lookupKeyName.'}';
            }

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

        $parameters->each(fn ($p) => $route = Str::replace(($s = Str::plural($p)), $s.'{'.$p.'}', $route));

        return new ActionMeta(
            action: $action,
            model: $modelClass,
            route: $route,
            routeName: $routeName,
            httpMethod: static::getPredefinedProperty($action, 'method', $httpMethod),
            middleware: static::getPredefinedProperty($action, 'middleware', ['web']),
        );
    }

    public static function params(string $action): array
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
        $dir = static::dir();
        $actionMetas = (
            static::classes($dir)
                ->map(fn ($fp, $action) => static::meta($action))
                ->filter(fn ($am) => $am->hasRoute())
        );

        $actionMetas->each(function ($meta) {
            $method = $meta->httpMethod;

            Route::middleware($meta->middleware)
                ->$method($meta->route, $meta->action)
                ->name($meta->routeName);
        });
    }

    public static function classes(string $dir): Collection
    {
        $namespace = collect(explode('/', $dir))->map('ucfirst')->join('\\');
        $classNames = [];
        $files = (new Filesystem)->allFiles(base_path($dir));

        foreach ($files as $file) {
            $path = $file->getPathName();
            $className = explode("{$dir}/", $path)[1];
            $className = Str::remove('.php', $className);
            $className = Str::replace('/', '\\', $className);
            $className = "{$namespace}\\{$className}";

            $classNames[$className] = $path;
        }

        return collect($classNames);
    }

    public static function anonymousClasses(): Collection
    {
        $dir = static::dir();

        return (
            static::classes($dir)
                ->map(function ($path, $className): null|array {

                    if (gettype($class = require($path)) !== 'object') {
                        return null;
                    }

                    $class = get_class($class);

                    if (! Str::contains($class, '@anonymous')) {
                        return null;
                    }

                    return [$className => $class];
                })
                ->whereNotNull()
                ->collapse()
        );
    }
}
