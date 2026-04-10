<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'membership_type',
        'member_since',
        'total_pledged',
        'total_paid',
        'outstanding_balance',
        'qb_customer_id',
        'qb_sync_token',
        'qb_last_sync_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'member_since'        => 'date',
            'total_pledged'       => 'decimal:2',
            'total_paid'          => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'qb_last_sync_at'     => 'datetime',

        ];
    }

    // Relationships

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'membership_type', 'slug')
                    ->where('tenant_id', $this->tenant_id ?? null);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(FamilyEmail::class);
    }

    public function primaryEmail(): HasOne
    {
        return $this->hasOne(FamilyEmail::class)->where('is_primary', true);
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(Pledge::class)->orderByDesc('invoice_date');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class);
    }

    public function yahrtzeits(): BelongsToMany
    {
        return $this->belongsToMany(Yahrtzeit::class, 'family_yahrtzeit')
                    ->withTimestamps()
                    ->orderBy('hebrew_month')
                    ->orderBy('hebrew_day');
    }

    // Scopes

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhereHas('emails', fn($e) => $e->where('email', 'like', "%{$term}%"))
              ->orWhere('city', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeWithBalance($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    public function scopeByMembershipType($query, string $slug)
    {
        return $query->where('membership_type', $slug);
    }

    // Helpers

    public function recalculateBalance(): void
    {
        $this->total_paid          = $this->payments()->completed()->sum('amount');
        $this->outstanding_balance = $this->pledges()->sum('balance');
        $this->save();
    }

    public function getPrimaryEmailStringAttribute(): ?string
    {
        return $this->primaryEmail?->email ?? $this->emails()->first()?->email;
    }
}
