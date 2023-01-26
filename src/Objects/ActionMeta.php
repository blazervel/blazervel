<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Objects;

use Illuminate\Support\Str;

class ActionMeta
{
    public string $action;

    public string $name;

    public string $slug;

    public string|null $model;

    public string|null $route;

    public string|null $routeName;
    
    public string|null $httpMethod;

    /**
     * @return string|string[]|null
     */
    public string|array|null $middleware;

    /**
     * @param string|string[] $middleware
     */
    public function __construct(
        string $action,
        string|null $model,
        string|null $route,
        string|null $routeName,
        string|null $httpMethod,
        string|array|null $middleware,
    ) {
        $this->action = $action;
        $this->name = class_basename($action);
        $this->slug = Str::snake($this->name, '-');

        $this->model = $model;
        $this->route = $route;
        $this->routeName = $routeName;
        $this->httpMethod = $httpMethod;
        $this->middleware = $middleware;
    }

    public function hasRoute(): bool
    {
        return (
            $this->route &&
            $this->routeName &&
            $this->httpMethod &&
            $this->middleware
        );
    }
}