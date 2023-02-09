<?php

namespace Blazervel\Blazervel;

use Closure;
use Illuminate\Support\Collection;

final class ClosureCollection
{
    public function __construct(Closure ...$closures)
    {
        return new Collection($closures);
    }
}
