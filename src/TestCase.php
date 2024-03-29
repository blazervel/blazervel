<?php

namespace Blazervel\Blazervel;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Controller $controller;

    protected function mount(...$params): self
    {
        $controllerClass = get_called_class();
        $controllerClass = Str::replace('Tests\\Feature\\', 'App\\Http\\Blazervel\\', $controllerClass);

        $this->controller = new $controllerClass;

        return $this;
    }
}
