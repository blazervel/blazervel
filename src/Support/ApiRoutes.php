<?php

namespace Blazervel\Blazervel\Support;

use Blazervel\Blazervel\Actions as ActionActions;
use Blazervel\Blazervel\Actions\Models as ModelActions;
use Illuminate\Support\Facades\Route;

class ApiRoutes
{
    private string $endpointPrefix = '/api/blazervel';

    public static function register(): void
    {
        (new self)->registerRoutes();
    }

    public function registerRoutes(): void
    {
        Route::middleware('api')->group(function () {
            Route::prefix($this->endpointPrefix)->group(function() {
                // Model Action Routes
                Route::get(   'models/{model}',             ModelActions\Index::class);
                Route::post(  'models/{model}',             ModelActions\Store::class);
                Route::get(   'models/{model}/{id}',        ModelActions\Show::class);
                Route::put(   'models/{model}/{id}',        ModelActions\Update::class);
                Route::delete('models/{model}/{id}',        ModelActions\Destroy::class);
                Route::post(  'models/{model}/{id}/notify', ModelActions\Notify::class);

                // Action Routes
                Route::post(  'actions/{action}',           ActionActions\ResolveAction::class);
                Route::get(   'actions/{action}',           ActionActions\ResolveAction::class);
                Route::post(  'batch',                      ActionActions\Batch::class);
                Route::get(   'batch',                      ActionActions\Batch::class);
                Route::post(  'run-actions',                ActionActions\RunActions::class);
                Route::get(   'run-actions',                ActionActions\RunActions::class);

                // Controller Routes
                Route::post(  'controllers/{action}',       ActionActions\ResolveControllerAction::class);
                Route::get(   'controllers/{action}',       ActionActions\ResolveControllerAction::class);
            });
        });

        // Need to deprioritize this catch-all to be after main routes
        // Route::middleware('web')->group(function () {
        //     Route::any('{any}', ActionActions\ResolveController::class)->where('any', '.*');
        // });
    }
}