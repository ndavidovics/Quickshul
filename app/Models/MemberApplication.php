<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberApplication extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'status',
        'membership_type',
        'data',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'family_id',
    ];

    protected function casts(): array
    {
        return [
            'data'        => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function membershipLabel(): string
    {
        return match($this->membership_type) {
            'full_family'     => 'Full Family — $1,800',
            'associate'       => 'Associate — $750',
            'single'          => 'Single — $900',
            'first_year_free' => 'Complimentary — $0',
            default           => $this->membership_type,
        };
    }
}
