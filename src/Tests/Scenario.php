<?php

namespace Blazervel\Blazervel\Tests;

abstract class Scenario
{
    abstract public function handle(array $data = []): array;

    public static function run(array $data = []): array
    {
        $class = get_called_class();

        return (new $class($data))->handle();
    }
}
