<?php

namespace Blazervel\Blazervel\Support;

use Illuminate\Support\Collection;

class Helpers
{
    public static function arr(mixed ...$parameters)
    {
        return $parameters;
    }

    public static function col(mixed ...$parameters)
    {
        return new Collection(
            static::arr(...$parameters)
        );
    }
}
