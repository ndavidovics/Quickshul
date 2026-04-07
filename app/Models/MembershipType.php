<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    protected $fillable = ['tenant_id', 'slug', 'label', 'is_donor', 'sort_order', 'active'];

    protected $casts = ['is_donor' => 'boolean', 'active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
