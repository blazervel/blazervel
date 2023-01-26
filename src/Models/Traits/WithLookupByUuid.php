<?php

namespace Blazervel\Blazervel\Models\Traits;

use Illuminate\Support\Str;

trait WithLookupByUuid
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::orderedUuid();
        });
    }
}
