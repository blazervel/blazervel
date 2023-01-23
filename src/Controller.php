<?php

namespace Blazervel\Blazervel;

use Blazervel\Blazervel\Support\Controllers;
use ReflectionClass;
use ReflectionProperty;

abstract class Controller
{
    public function __invoke()
    {
        //
    }

    public static function __callStatic($name, $arguments)
    {
        $controllerClass = get_called_class();
        
        $configDefaults = Controllers::configDefaults($controllerClass);

        $controller = new $controllerClass;
        $protectedProps = (new ReflectionClass(get_called_class()))->getProperties(ReflectionProperty::IS_PROTECTED);
        $protectedProps = collect($protectedProps)->map(fn ($prop) => [($name = $prop->getName()) => $controller->$name])->collapse()->all();

        return (
            collect($configDefaults)
                ->map(fn ($default, $prop) => (
                    $protectedProps[$prop] ?? $default
                ))
                ->all()
        );
    }
}