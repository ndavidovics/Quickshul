<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        // Auto-apply tenant scope on all queries
        static::addGlobalScope(new TenantScope());

        // Auto-set tenant_id on creation
        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->bound('tenant')) {
                $model->tenant_id = app('tenant')->id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
