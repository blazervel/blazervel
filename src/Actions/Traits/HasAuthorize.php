<?php

namespace Blazervel\Blazervel\Actions\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Blazervel\Blazervel\Support\Actions;
use Illuminate\Support\Facades\Gate;

trait HasAuthorize
{
    protected function authorize(Model $model = null, User $user = null, string $action = null): void
    {
        $meta = Actions::meta(get_called_class());

        if ($meta->modelClass === null) {
            throw new Exception(
                "Blazervel says \"I tried authorizing a model based on this action's namespace but one doesn't exist...\""
            );
        }
        
        Gate::forUser(
            $user ?: request()->user()
        )->authorize(
            $action ?: $meta->action,
            $model ?: $meta->model ?: $meta->modelClass
        );
    }
}
