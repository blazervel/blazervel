<?php declare (strict_types=1);

namespace Blazervel\Blazervel;

use Blazervel\Blazervel\Actions\Traits\HasAuthorize;
use Blazervel\Blazervel\Actions\Traits\HasDispatch;
use Blazervel\Blazervel\Actions\Traits\HasValidate;

abstract class Action
{
    use HasAuthorize,
        HasValidate,
        HasDispatch;

    protected string $route;

    protected string $httpMethod;

    /**
     * @return string|string[]
     */
    protected string|array $middleware;

    public static function run(...$parameters)
    {
        $action = get_called_class();
        return (new $action)->__invoke(...$parameters);
    }
}
