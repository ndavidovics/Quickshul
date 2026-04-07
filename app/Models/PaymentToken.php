<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentToken extends Model
{
    use HasTenant;

    protected $fillable = ['tenant_id', 'token', 'family_id', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function isValid(): bool
    {
        return now()->lt($this->expires_at);
    }

    public static function generateFor(Family $family): self
    {
        // Issue a fresh 30-day token. Old tokens are left intact so links
        // already sent in previous emails remain valid until they naturally expire.
        return self::create([
            'token'      => bin2hex(random_bytes(32)),
            'family_id'  => $family->id,
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Return the newest valid token for this family, or create one if none exists.
     * Use this for previews and test sends so live payment links are never invalidated.
     */
    public static function getOrCreateFor(Family $family): self
    {
        $existing = self::where('family_id', $family->id)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return $existing ?? self::generateFor($family);
    }
}
