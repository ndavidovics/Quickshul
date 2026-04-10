<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    protected $fillable = ['tenant_id', 'slug', 'label', 'is_donor', 'qb_labels', 'sort_order', 'active'];

    protected $casts = ['is_donor' => 'boolean', 'active' => 'boolean', 'qb_labels' => 'array'];

    public function matchesQbLabel(string $label): bool
    {
        return in_array($label, $this->qb_labels ?? []);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
