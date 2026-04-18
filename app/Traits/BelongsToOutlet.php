<?php

namespace App\Traits;

use App\Models\Outlet;
use App\Models\Scopes\OutletScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOutlet
{
    /**
     * Boot the BelongsToOutlet trait.
     */
    protected static function bootBelongsToOutlet(): void
    {
        static::addGlobalScope(new OutletScope);

        static::creating(function ($model) {
            if (app()->has('outlet_id') && ! $model->outlet_id) {
                $model->outlet_id = app('outlet_id');
            }
        });
    }

    /**
     * Get the outlet that owns the model.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
