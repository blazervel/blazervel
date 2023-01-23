<?php

namespace Blazervel\Blazervel\Actions\Pages;

use Blazervel\Blazervel\Action;
use Blazervel\Blazervel\Actions\ResolveAction;
use Blazervel\Blazervel\Support\Actions;
use Blazervel\Blazervel\Actions\Config;
use Blazervel\Blazervel\WithBlazervel;
use Illuminate\Http\Request;

class Data extends Action
{
    use WithBlazervel;

    public function handle(Request $request, string $url)
    {
        $action    = Actions::urlAction($url);
        $params    = Actions::urlParams($url);
        $component = Actions::actionComponent($action);
        $response  = ResolveAction::run(new Request($params), $action);

        $data = [
            'action' => $action,
            'props' => $response,
            'config' => Config\App::run(),
            'componentName' => $component
        ];

        return $data;
    }
}