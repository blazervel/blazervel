<?php

namespace Blazervel\Blazervel\Actions\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

trait HasValidate
{
    final protected function validate(array $rules, array $data = null): array
    {
        $data = $data ?: Request::instance()->all();
        
        Validator::make($data, $rules)->validate();

        return $data;
    }
}
