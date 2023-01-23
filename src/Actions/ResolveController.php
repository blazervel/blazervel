<?php

namespace Blazervel\Blazervel\Actions;

use Illuminate\Http\Request;

class ResolveController
{
    public function __invoke(Request $request)
    {
        return view('blazervel::app');
    }
}