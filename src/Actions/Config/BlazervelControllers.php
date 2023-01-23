<?php

namespace Blazervel\Blazervel\Actions\Config;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Blazervel\Blazervel\Controller;
use ReflectionProperty;

class BlazervelControllers extends Config
{
    public function generate(): array
    {
        $path = app_path('Http/Blazervel');

        if (!File::exists($path)) {
            return [];
        }

        return [
            'controllers' => (
                collect(File::allFiles($path))
                    ->map(fn ($file) => (
                        Str::replace('/', '\\', Str::replace(app_path(), 'App', Str::remove('.php', $file->getPathname())))
                    ))
                    ->filter(fn ($class) => is_subclass_of($class, Controller::class))
                    ->filter(fn ($class) => (new ReflectionClass($class))->isAbstract() === false)
                    ->map(fn ($class) => [$class => array_merge($class::getBlazervelConfig(), [
                        'key' => Str::remove('App.', Str::replace('\\', '.', $class)),
                        'methods' => $this->getControllerMethods($class),
                        'properties' => $this->getControllerProperties($class)
                    ])])
                    ->collapse()
                    ->all()
            )
        ];
    }

    private function getControllerMethods(string $class): array
    {
        $methods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);

        return (
            collect($methods)
                ->where('class', $class)
                ->filter(fn ($m) => (
                    ($name = $m->getName()) === '__invoke' ||
                    !Str::startsWith($name, '_')
                ))
                ->map(fn ($m) => [
                    $m->getName() => [
                        'returnType' => $this->getMethodReturnType($m),
                        'parameters' => (
                            collect($m->getParameters())
                                ->map(fn ($p) => [
                                    $p->getName() => [
                                        'type' => $p->getType(),
                                        'required' => !$p->isOptional()
                                    ]
                                ])
                                ->collapse()
                                ->all()
                        )
                    ]
                ])
                ->collapse()
                ->all()
        );
    }

    private function getControllerProperties(string $class): array
    {
        $properties = (new ReflectionClass($class))->getProperties(ReflectionProperty::IS_PUBLIC);

        return (
            collect($properties)
                ->where('class', $class)
                ->map(fn ($p) => [
                    $p->getName() => [
                        'type' => $p->getType()->getName(),
                        'value' => $p->getDefaultValue(),
                        'allowsNull' => $p->getType()->allowsNull()
                    ]
                ])
                ->collapse()
                ->all()
        );
    }
}