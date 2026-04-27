<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'event_date',
        'family_max',
        'qb_item_id',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'family_max' => 'decimal:2',
    ];

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EventPayment::class);
    }

    public function completedPayments(): HasMany
    {
        return $this->hasMany(EventPayment::class)->where('status', 'completed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function generateSlug(string $name, ?int $tenantId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n    = 1;

        $tid = $tenantId ?? (app()->bound('tenant') ? app('tenant')->id : null);

        while (self::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . ++$n;
        }

        return $slug;
    }
}
