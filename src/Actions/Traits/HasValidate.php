<?php

namespace Blazervel\Blazervel\Actions\Traits;

use Illuminate\Support\Facades\Validator;

trait HasValidate
{
    protected function validate(array $rules, array $data = null): array
    {
        $data = $data ?: request()->all();
        
        Validator::make($data, $rules)->validate();

        return $data;
    }
}
