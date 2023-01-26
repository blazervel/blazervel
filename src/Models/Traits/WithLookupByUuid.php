<?php declare (strict_types=1);

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
        parent::booted();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::orderedUuid();
        });
    }
}
